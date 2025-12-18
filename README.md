# AWS Four-Tier Mailing Application - Technical Project Report

**Project Name:** InfraMail 
**Architecture Type:** Four-Tier Multi-AZ Production Deployment  
**Cloud Provider:** Amazon Web Services (AWS)  
**Region:** US-West-1 (N. California)  
**Date:** December 2025  
**Author:** Omari

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Architecture Overview](#2-architecture-overview)
3. [Network Infrastructure](#3-network-infrastructure)
4. [Application Components & Files](#4-application-components--files)
5. [AWS Services Deep Dive](#5-aws-services-deep-dive)
6. [Security Implementation](#6-security-implementation)
7. [High Availability & Fault Tolerance](#7-high-availability--fault-tolerance)
8. [Deployment Workflow](#8-deployment-workflow)
9. [Cost Considerations](#9-cost-considerations)
10. [Conclusion](#10-conclusion)

---

## 1. Executive Summary

### 1.1 Project Purpose

The Omari Mailing Service is a production-grade PHP web application designed to manage employee mailing addresses. The application demonstrates enterprise-level AWS architecture patterns including:

- **Multi-tier separation** of concerns (Web, Application, Database layers)
- **High availability** across multiple Availability Zones
- **Auto-scaling** capabilities for handling variable workloads
- **Security-in-depth** with layered security groups
- **Managed database services** with automated failover

### 1.2 Business Functionality

The application provides a simple yet effective employee directory system:
- Add new employee records (Name + Mailing Address)
- Store data persistently in a relational database
- View all stored employee records in a web interface
- Automatic table creation on first use

### 1.3 Technical Stack

| Component | Technology |
|-----------|------------|
| Frontend/Proxy | Apache2 (Ubuntu 20.04) |
| Application | PHP 8.0 on Apache HTTPD |
| Database | MySQL 8.x (AWS RDS) |
| Operating Systems | Ubuntu 20.04 LTS (Web), Amazon Linux 2 (App) |
| Load Balancing | Application Load Balancer (ALB) |
| DNS | Amazon Route 53 |

---

## 2. Architecture Overview

### 2.1 Four-Tier Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              INTERNET                                        │
│                                  │                                           │
│                          ┌───────▼───────┐                                  │
│                          │   Route 53    │  (DNS Resolution)                │
│                          │  DNS Service  │                                  │
│                          └───────┬───────┘                                  │
└──────────────────────────────────┼──────────────────────────────────────────┘
                                   │
┌──────────────────────────────────▼──────────────────────────────────────────┐
│  TIER 1: NAT/ALB LAYER (Public Subnets)                                     │
│  ┌─────────────────────────────────────────────────────────────────────┐    │
│  │                    Internet Gateway (IGW)                            │    │
│  └─────────────────────────────┬───────────────────────────────────────┘    │
│                                │                                             │
│    ┌───────────────────────────┴───────────────────────────┐                │
│    │                                                        │                │
│    ▼                                                        ▼                │
│  ┌─────────────────────┐                    ┌─────────────────────┐         │
│  │  NAT/ALB Subnet 1   │                    │  NAT/ALB Subnet 2   │         │
│  │   (us-west-1a)      │                    │   (us-west-1c)      │         │
│  │  ┌───────────────┐  │                    │  ┌───────────────┐  │         │
│  │  │ NAT Gateway 1 │  │                    │  │ NAT Gateway 2 │  │         │
│  │  └───────────────┘  │                    │  └───────────────┘  │         │
│  │  ┌───────────────┐  │                    │                     │         │
│  │  │ Bastion Host  │  │                    │                     │         │
│  │  └───────────────┘  │                    │                     │         │
│  └─────────────────────┘                    └─────────────────────┘         │
│                    │                                │                        │
│                    └────────────┬───────────────────┘                        │
│                                 ▼                                            │
│                    ┌─────────────────────────┐                               │
│                    │   Frontend ALB          │                               │
│                    │   (Internet-facing)     │                               │
│                    └────────────┬────────────┘                               │
└─────────────────────────────────┼───────────────────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────────────────┐
│  TIER 2: WEB/PROXY LAYER (Public Subnets with Auto Scaling)                 │
│                                                                              │
│    ┌─────────────────────────────────────────────────────────────────┐      │
│    │                     Auto Scaling Group                          │      │
│    │  ┌─────────────────────┐          ┌─────────────────────┐      │      │
│    │  │  Web Subnet 1       │          │  Web Subnet 2       │      │      │
│    │  │  (us-west-1a)       │          │  (us-west-1c)       │      │      │
│    │  │  ┌───────────────┐  │          │  ┌───────────────┐  │      │      │
│    │  │  │ Web Server 1  │  │          │  │ Web Server 2  │  │      │      │
│    │  │  │ (Apache Proxy)│  │          │  │ (Apache Proxy)│  │      │      │
│    │  │  └───────────────┘  │          │  └───────────────┘  │      │      │
│    │  └─────────────────────┘          └─────────────────────┘      │      │
│    └─────────────────────────────────────────────────────────────────┘      │
│                                  │                                           │
│                                  ▼                                           │
│                    ┌─────────────────────────┐                               │
│                    │   Backend ALB           │                               │
│                    │   (Internal)            │                               │
│                    └────────────┬────────────┘                               │
└─────────────────────────────────┼───────────────────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────────────────┐
│  TIER 3: APPLICATION LAYER (Private Subnets with Auto Scaling)              │
│                                                                              │
│    ┌─────────────────────────────────────────────────────────────────┐      │
│    │                     Auto Scaling Group                          │      │
│    │  ┌─────────────────────┐          ┌─────────────────────┐      │      │
│    │  │  App Subnet 1       │          │  App Subnet 2       │      │      │
│    │  │  (us-west-1a)       │          │  (us-west-1c)       │      │      │
│    │  │  ┌───────────────┐  │          │  ┌───────────────┐  │      │      │
│    │  │  │ App Server 1  │  │          │  │ App Server 2  │  │      │      │
│    │  │  │ (PHP/Apache)  │  │          │  │ (PHP/Apache)  │  │      │      │
│    │  │  └───────────────┘  │          │  └───────────────┘  │      │      │
│    │  └─────────────────────┘          └─────────────────────┘      │      │
│    └─────────────────────────────────────────────────────────────────┘      │
│                                  │                                           │
└─────────────────────────────────┼───────────────────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────────────────┐
│  TIER 4: DATABASE LAYER (Private Subnets with Multi-AZ)                     │
│                                                                              │
│    ┌─────────────────────┐          ┌─────────────────────┐                 │
│    │  DB Subnet 1        │          │  DB Subnet 2        │                 │
│    │  (us-west-1a)       │          │  (us-west-1c)       │                 │
│    │  ┌───────────────┐  │          │  ┌───────────────┐  │                 │
│    │  │ RDS Primary   │◄─┼──────────┼─►│ RDS Standby   │  │                 │
│    │  │ (MySQL)       │  │  Sync    │  │ (MySQL)       │  │                 │
│    │  └───────────────┘  │  Repl.   │  └───────────────┘  │                 │
│    └─────────────────────┘          └─────────────────────┘                 │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.2 Request Flow

1. **User Request** → Browser sends HTTP request to `www.venturamailing.com`
2. **DNS Resolution** → Route 53 resolves domain to Frontend ALB DNS
3. **Frontend Load Balancer** → Distributes traffic to Web Servers
4. **Web Server (Reverse Proxy)** → Apache2 forwards request to Backend ALB
5. **Backend Load Balancer** → Distributes traffic to App Servers
6. **Application Server** → PHP processes request, queries database
7. **Database** → MySQL RDS returns data
8. **Response** → Data flows back through all layers to user

---

## 3. Network Infrastructure

### 3.1 VPC Configuration

| Parameter | Value |
|-----------|-------|
| VPC Name | `Prod-VPC` |
| CIDR Block | `10.0.0.0/16` |
| Region | `us-west-1` (N. California) |
| Availability Zones | `us-west-1a`, `us-west-1c` |
| Total Available IPs | 65,536 |

### 3.2 Subnet Design

The network is divided into 8 subnets across 4 layers and 2 availability zones:

| Layer | Subnet Name | CIDR Block | AZ | Type | IPs | Purpose |
|-------|-------------|------------|-----|------|-----|---------|
| NAT/ALB | `Prod-NAT-ALB-Subnet-1` | `10.0.5.0/28` | us-west-1a | Public | 11 | NAT Gateway, ALB, Bastion |
| NAT/ALB | `Prod-NAT-ALB-Subnet-2` | `10.0.10.0/28` | us-west-1c | Public | 11 | NAT Gateway, ALB |
| Web | `Prod-Webserver-Subnet-1` | `10.0.15.0/23` | us-west-1a | Public | 507 | Web/Proxy Servers |
| Web | `Prod-Webserver-Subnet-2` | `10.0.20.0/23` | us-west-1c | Public | 507 | Web/Proxy Servers |
| App | `Prod-Appserver-Subnet-1` | `10.0.25.0/23` | us-west-1a | Private | 507 | Application Servers |
| App | `Prod-Appserver-Subnet-2` | `10.0.30.0/23` | us-west-1c | Private | 507 | Application Servers |
| DB | `Prod-db-Subnet-1` | `10.0.35.0/28` | us-west-1a | Private | 11 | RDS Primary |
| DB | `Prod-db-Subnet-2` | `10.0.40.0/28` | us-west-1c | Private | 11 | RDS Standby |

**Total Usable IPs:** 2,072

### 3.3 Route Tables

| Route Table | Associated Subnet | Destination | Target |
|-------------|-------------------|-------------|--------|
| `Prod-NAT-ALB-Public-RT-1` | NAT/ALB Subnet 1 | 0.0.0.0/0 | Internet Gateway |
| `Prod-NAT-ALB-Public-RT-2` | NAT/ALB Subnet 2 | 0.0.0.0/0 | Internet Gateway |
| `Prod-Webserver-RT-1` | Webserver Subnet 1 | 0.0.0.0/0 | Internet Gateway |
| `Prod-Webserver-RT-2` | Webserver Subnet 2 | 0.0.0.0/0 | Internet Gateway |
| `Prod-Appserver-RT-1` | Appserver Subnet 1 | 0.0.0.0/0 | NAT Gateway 1 |
| `Prod-Appserver-RT-2` | Appserver Subnet 2 | 0.0.0.0/0 | NAT Gateway 2 |
| `Prod-Database-RT-1` | Database Subnet 1 | 0.0.0.0/0 | NAT Gateway 1 |
| `Prod-Database-RT-2` | Database Subnet 2 | 0.0.0.0/0 | NAT Gateway 2 |

### 3.4 Internet & NAT Gateways

**Internet Gateway:**
- Name: `Prod-VPC-IGW`
- Attached to: `Prod-VPC`
- Purpose: Enables internet access for public subnets

**NAT Gateways (Redundant):**

| NAT Gateway | Subnet | Elastic IP | Purpose |
|-------------|--------|------------|---------|
| `Prod-NAT-Gateway-1` | Prod-NAT-ALB-Subnet-1 | Allocated | Outbound for AZ-1a private subnets |
| `Prod-NAT-Gateway-2` | Prod-NAT-ALB-Subnet-2 | Allocated | Outbound for AZ-1c private subnets |

---

## 4. Application Components & Files

### 4.1 Project File Structure

```
aws-real-world-projects-four-tier-mailing-app-project/
│
├── app-web-db-config/                    # Configuration files for deployment
│   ├── 000-default.conf                  # Apache reverse proxy config
│   ├── dbinfo.inc                        # Database connection credentials
│   └── OmariMailingApp.php               # Main PHP application
│
├── appservers-database-config/
│   └── app-database-config.md            # Database configuration instructions
│
├── appservers-startup-scripts/
│   ├── app-automation.sh                 # App server bootstrap script
│   ├── OmariMailingApp.php               # Copy of PHP application
│   └── archive/
│       └── app-automation.sh             # Archived/manual version
│
├── webservers-reverse-proxy-config/
│   ├── 000-default.conf                  # Reverse proxy Apache config
│   ├── README.md                         # Web server setup instructions
│   └── web-automation.sh                 # Web server bootstrap script
│
├── prod-env-project-architecture.png     # Architecture diagram
├── prod-env-subnetting.png               # Network/subnet diagram
└── README.md                             # Main project documentation
```

### 4.2 Main Application File: `OmariMailingApp.php`

**Location:** `/var/www/html/OmariMailingApp.php` (on App Servers)

**Purpose:** The core PHP application that handles all business logic.

```php
<?php include "../inc/dbinfo.inc"; ?>
<html>
<body>
<h1>Omari Mailing Service</h1>
<?php
  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the EMPLOYEES table exists. */
  VerifyEmployeesTable($connection, DB_DATABASE);

  /* If input fields are populated, add a row to the EMPLOYEES table. */
  $employee_name = htmlentities($_POST['NAME']);
  $employee_address = htmlentities($_POST['ADDRESS']);

  if (strlen($employee_name) || strlen($employee_address)) {
    AddEmployee($connection, $employee_name, $employee_address);
  }
?>
```

**Application Logic Flow:**

1. **Database Connection**
   - Includes `dbinfo.inc` for credentials
   - Establishes MySQLi connection to RDS
   - Selects the `phpappdatabase` database

2. **Table Verification**
   - Calls `VerifyEmployeesTable()` function
   - Checks if `EMPLOYEES` table exists using `information_schema`
   - Creates table automatically if not present:
   ```sql
   CREATE TABLE EMPLOYEES (
       ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
       NAME VARCHAR(45),
       ADDRESS VARCHAR(90)
   )
   ```

3. **Form Processing**
   - Receives POST data from HTML form
   - Sanitizes input using `htmlentities()`
   - Calls `AddEmployee()` to insert new records
   - Uses `mysqli_real_escape_string()` to prevent SQL injection

4. **Data Display**
   - Executes `SELECT * FROM EMPLOYEES`
   - Iterates through results with `mysqli_fetch_row()`
   - Renders HTML table with ID, Name, Address columns

5. **Cleanup**
   - Frees result set memory
   - Closes database connection

### 4.3 Database Configuration: `dbinfo.inc`

**Location:** `/var/www/inc/dbinfo.inc` (on App Servers)

**Purpose:** Stores database connection parameters as PHP constants.

```php
<?php

define('DB_SERVER', 'prod-database.cee2jhm51ydc.us-west-1.rds.amazonaws.com');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', 'admin12345');
define('DB_DATABASE', 'phpappdatabase');

?>
```

**Configuration Parameters:**

| Constant | Description | Example Value |
|----------|-------------|---------------|
| `DB_SERVER` | RDS endpoint (no port) | `omari-database.xxx.us-west-1.rds.amazonaws.com` |
| `DB_USERNAME` | Master username | `admin` |
| `DB_PASSWORD` | Master password | `your_secure_password` |
| `DB_DATABASE` | Initial database name | `omaridatabase` |

**Security Note:** This file contains sensitive credentials and should be:
- Stored in S3 with restricted access
- Never committed to version control
- Ideally replaced with AWS Secrets Manager

### 4.4 Apache Reverse Proxy Configuration: `000-default.conf`

**Location:** `/etc/apache2/sites-available/000-default.conf` (on Web Servers)

**Purpose:** Configures Apache as a reverse proxy to forward traffic to the backend.

```apache
<VirtualHost *:80>
    ProxyPass / http://PROD_BACKEND_LOAD_BALANCER_DNS/OmariMailingApp.php/
</VirtualHost>
```

**How It Works:**

1. **Virtual Host**: Listens on port 80 for all incoming requests
2. **ProxyPass Directive**: 
   - Intercepts all requests to `/` 
   - Forwards them to the Backend Load Balancer
   - Appends `/OmariMailingApp.php/` to the request path

**Required Apache Modules:**
- `proxy` - Base proxy module
- `proxy_http` - HTTP proxy support

### 4.5 Web Server Bootstrap Script: `web-automation.sh`

**Purpose:** User data script for EC2 Launch Template (Web Servers)

**Operating System:** Ubuntu 20.04 LTS

```bash
#!/bin/bash
# Install and Setup Apache2 and PHP Version 7
sudo apt-get -y install apache2
sudo locale-gen en_US.UTF-8
export LANG=en_US.UTF-8
sudo apt-get update 
sudo apt-get install -y software-properties-common python-software-properties
sudo LC_ALL=en_US.UTF-8 add-apt-repository -y ppa:ondrej/php
sudo apt-get update 
sudo apt-get -y install php7.0 php7.0-curl php7.0-bcmath php7.0-intl php7.0-gd \
    php7.0-dom php7.0-mcrypt php7.0-iconv php7.0-xsl php7.0-mbstring php7.0-ctype \
    php7.0-zip php7.0-pdo php7.0-xml php7.0-bz2 php7.0-calendar php7.0-exif \
    php7.0-fileinfo php7.0-json php7.0-mysqli php7.0-mysql php7.0-posix \
    php7.0-tokenizer php7.0-xmlwriter php7.0-xmlreader php7.0-phar php7.0-soap \
    php7.0-mysql php7.0-fpm libapache2-mod-php7.0
sudo sed -i -e"s/^memory_limit\s*=\s*128M/memory_limit = 512M/" /etc/php/7.0/apache2/php.ini
sudo apt-get install wget -y

# Restart and Enable the Apache2 Service
sudo a2enmod rewrite
sudo a2enmod headers
sudo service apache2 restart
sudo service apache2 enable

# Install AWSCLI
sudo apt-get install awscli -y

# Copy the Apache2 Proxy Config File From S3
sudo rm /etc/apache2/sites-available/000-default.conf
sudo aws s3 cp s3://prod-proxy-app-db-config-aak-10/000-default.conf /etc/apache2/sites-available/

# Enable Apache2 Reverse Proxy
sudo a2enmod proxy 
sudo a2enmod proxy_http
sudo systemctl restart apache2
```

**Script Execution Steps:**

| Step | Action | Purpose |
|------|--------|---------|
| 1 | Install Apache2 | Web server installation |
| 2 | Configure locale | Set UTF-8 encoding |
| 3 | Add PHP PPA | Access to PHP 7.0 packages |
| 4 | Install PHP 7.0 + modules | PHP runtime with extensions |
| 5 | Increase memory limit | 128M → 512M for PHP |
| 6 | Enable Apache modules | rewrite, headers |
| 7 | Install AWS CLI | S3 access capability |
| 8 | Download proxy config | Get 000-default.conf from S3 |
| 9 | Enable proxy modules | proxy, proxy_http |
| 10 | Restart Apache | Apply all configurations |

### 4.6 Application Server Bootstrap Script: `app-automation.sh`

**Purpose:** User data script for EC2 Launch Template (App Servers)

**Operating System:** Amazon Linux 2

```bash
#!/bin/bash
# Install and Setup Apache2, PHP and MySQL Client

# Update system packages
sudo yum update -y

# Install PHP 8.0 and MariaDB client
sudo amazon-linux-extras install php8.0 mariadb10.5 -y

# Install Apache HTTPD
sudo yum install -y httpd

# Start and enable Apache
sudo systemctl start httpd
sudo systemctl enable httpd

# Set file permissions for Apache
sudo usermod -a -G apache ec2-user
sudo chown -R ec2-user:apache /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec sudo chmod 2775 {} \;
find /var/www -type f -exec sudo chmod 0664 {} \;

# Create inc directory for database config
mkdir /var/www/inc

# Download configuration files from S3
aws s3 cp s3://prod-proxy-app-db-config-aak-10/dbinfo.inc /var/www/inc
aws s3 cp s3://prod-proxy-app-db-config-aak-10/OmariMailingApp.php /var/www/html
```

**Script Execution Steps:**

| Step | Action | Purpose |
|------|--------|---------|
| 1 | `yum update` | Update all packages |
| 2 | Install PHP 8.0 | PHP runtime from Amazon Extras |
| 3 | Install MariaDB 10.5 | MySQL-compatible client |
| 4 | Install httpd | Apache web server |
| 5 | Start/enable httpd | Run Apache on boot |
| 6 | Configure permissions | Allow ec2-user to manage /var/www |
| 7 | Create /var/www/inc | Directory for dbinfo.inc |
| 8 | Download dbinfo.inc | Database credentials from S3 |
| 9 | Download PHP app | Application code from S3 |

---

## 5. AWS Services Deep Dive

### 5.1 Amazon VPC (Virtual Private Cloud)

**Purpose:** Provides isolated network infrastructure for the entire application.

**Components Created:**
- 1 VPC with CIDR 10.0.0.0/16
- 8 Subnets (4 public, 4 private)
- 8 Route Tables
- 1 Internet Gateway
- 2 NAT Gateways
- 2 Elastic IPs

**Key Features Used:**
- Custom CIDR block allocation
- Multiple Availability Zone deployment
- Public/Private subnet separation
- Route table associations

### 5.2 Amazon EC2 (Elastic Compute Cloud)

**Purpose:** Hosts web servers, application servers, and bastion host.

**Instance Specifications:**

| Server Type | AMI | Instance Type | OS |
|-------------|-----|---------------|-----|
| Bastion Host | Ubuntu 20.04 | t2.micro | Ubuntu |
| Web Servers | Ubuntu 20.04 | t2.micro | Ubuntu |
| App Servers | Amazon Linux 2 | t2.micro | Amazon Linux |

**Launch Templates Created:**

1. **Prod-Webservers-LT**
   - AMI: Ubuntu 20.04 LTS
   - Instance type: t2.micro
   - IAM Role: EC2-AmazonS3ReadOnlyAccess
   - User Data: web-automation.sh
   - Security Group: Webservers-Security-Group

2. **Prod-Appservers-LT**
   - AMI: Amazon Linux 2
   - Instance type: t2.micro
   - IAM Role: EC2-AmazonS3ReadOnlyAccess
   - User Data: app-automation.sh
   - Security Group: Appservers-Security-Group

### 5.3 Amazon RDS (Relational Database Service)

**Purpose:** Managed MySQL database with automatic failover.

**Database Configuration:**

| Parameter | Value |
|-----------|-------|
| Engine | MySQL (latest version) |
| Template | Production |
| Deployment | Multi-AZ DB Instance |
| DB Identifier | `prod-database` |
| Master Username | `admin` |
| Instance Class | db.t2.micro (Burstable) |
| Storage Type | General Purpose SSD (gp3) |
| Allocated Storage | 30 GB |
| Max Storage | 1000 GB (auto-scaling enabled) |
| Initial Database | `phpappdatabase` |
| Public Access | No |
| Backup | Enabled (automated) |
| Encryption | Enabled |
| Deletion Protection | Disabled (for testing) |

**Multi-AZ Deployment:**
- Primary instance in `us-west-1a`
- Standby replica in `us-west-1c`
- Synchronous replication
- Automatic failover (2-3 minutes)

**DB Subnet Group:**
- Name: `prod-db-subnet-group`
- Subnets: Prod-db-Subnet-1, Prod-db-Subnet-2

### 5.4 Elastic Load Balancing (ALB)

**Purpose:** Distributes incoming traffic across multiple targets.

**Frontend Load Balancer:**

| Parameter | Value |
|-----------|-------|
| Name | `Prod-Frontend-LB` |
| Type | Application Load Balancer |
| Scheme | Internet-facing |
| IP Type | IPv4 |
| Subnets | Prod-NAT-ALB-Subnet-1, Prod-NAT-ALB-Subnet-2 |
| Security Group | Frontend-LB-Security-Group |
| Listener | HTTP:80 |
| Target Group | Frontend-LB-HTTP-TG |

**Backend Load Balancer:**

| Parameter | Value |
|-----------|-------|
| Name | `Prod-Backend-LB` |
| Type | Application Load Balancer |
| Scheme | Internal |
| IP Type | IPv4 |
| Subnets | Prod-Webserver-Subnet-1, Prod-Webserver-Subnet-2 |
| Security Group | Backend-LB-Security-Group |
| Listener | HTTP:80 |
| Target Group | Backend-LB-HTTP-TG |

**Target Groups:**

| Target Group | Protocol | Port | Health Check Path |
|--------------|----------|------|-------------------|
| Frontend-LB-HTTP-TG | HTTP | 80 | /OmariMailingApp.php |
| Backend-LB-HTTP-TG | HTTP | 80 | /OmariMailingApp.php |

### 5.5 Auto Scaling

**Purpose:** Automatically adjusts capacity based on demand.

**Web Server Auto Scaling Group:**

| Parameter | Value |
|-----------|-------|
| Name | `prod-webservers-autoscaling-group` |
| Launch Template | Prod-Webservers-LT |
| VPC | Prod-VPC |
| Subnets | Webserver Subnet 1 & 2 |
| Min Capacity | 2 |
| Desired Capacity | 2 |
| Max Capacity | 5 |
| Target Group | Frontend-LB-HTTP-TG |
| Health Check | EC2 + ELB |

**App Server Auto Scaling Group:**

| Parameter | Value |
|-----------|-------|
| Name | `prod-appservers-autoscaling-group` |
| Launch Template | Prod-Appservers-LT |
| VPC | Prod-VPC |
| Subnets | Appserver Subnet 1 & 2 |
| Min Capacity | 2 |
| Desired Capacity | 2 |
| Max Capacity | 5 |
| Target Group | Backend-LB-HTTP-TG |
| Health Check | EC2 + ELB |

**Scaling Policy:**
- Type: Target Tracking
- Metric: Average CPU Utilization
- Target Value: 80%
- Scale Out: Add instances when CPU > 80%
- Scale In: Remove instances when CPU < 80%

### 5.6 Amazon S3 (Simple Storage Service)

**Purpose:** Stores configuration files for EC2 bootstrap.

**Bucket Configuration:**

| Parameter | Value |
|-----------|-------|
| Name | `prod-proxy-app-db-config-{your-name}-{month}` |
| Region | us-west-1 |
| Object Ownership | ACLs disabled |
| Block Public Access | Enabled (all blocked) |
| Versioning | Enabled |
| Encryption | Enabled (SSE-S3) |

**Files Stored:**
1. `000-default.conf` - Apache reverse proxy config
2. `dbinfo.inc` - Database credentials
3. `OmariMailingApp.php` - PHP application

### 5.7 Amazon Route 53

**Purpose:** DNS management and health checking.

**DNS Configuration:**

| Record Type | Name | Value | Routing Policy |
|-------------|------|-------|----------------|
| CNAME | www | Prod-Frontend-LB DNS | Simple |

**Health Check:**

| Parameter | Value |
|-----------|-------|
| Name | Prod-Webapp-HC |
| Monitor | Endpoint |
| Specify By | Domain Name |
| Protocol | HTTP |
| Port | 80 |
| Path | /OmariMailingApp.php |
| SNS Topic | PHP-Webapp-SNS-Topic |

### 5.8 AWS IAM (Identity and Access Management)

**Purpose:** Manages permissions for EC2 instances to access S3.

**IAM Role:**

| Parameter | Value |
|-----------|-------|
| Role Name | EC2-AmazonS3ReadOnlyAccess |
| Trusted Entity | EC2 Service |
| Policy | AmazonS3ReadOnlyAccess |
| Use Case | Download configs from S3 |

### 5.9 AWS Certificate Manager (Optional)

**Purpose:** Provision SSL/TLS certificates for HTTPS.

**Configuration:**
- Certificate Type: Public
- Domain Name: www.your-domain.com
- Validation: DNS validation via Route 53

### 5.10 Additional AWS Services

| Service | Purpose |
|---------|---------|
| **AWS KMS** | Encryption key management for RDS |
| **CloudWatch** | Monitoring, logs, and alarms |
| **CloudTrail** | API audit logging |
| **SNS** | Health check notifications |

---

## 6. Security Implementation

### 6.1 Security Groups (Firewall Rules)

Security groups implement a **defense-in-depth** strategy where each layer only accepts traffic from the previous layer.

**1. Bastion-Host-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 22 | TCP | Your IP / 0.0.0.0/0 | SSH access |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

**2. Frontend-LB-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 80 | TCP | 0.0.0.0/0 | HTTP from internet |
| Inbound | 443 | TCP | 0.0.0.0/0 | HTTPS from internet |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

**3. Webservers-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 80 | TCP | Frontend-LB-SG | HTTP from Frontend LB |
| Inbound | 443 | TCP | Frontend-LB-SG | HTTPS from Frontend LB |
| Inbound | 22 | TCP | Bastion-Host-SG | SSH from Bastion |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

**4. Backend-LB-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 80 | TCP | Webservers-SG | HTTP from Web Servers |
| Inbound | 443 | TCP | Webservers-SG | HTTPS from Web Servers |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

**5. Appservers-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 80 | TCP | Backend-LB-SG | HTTP from Backend LB |
| Inbound | 443 | TCP | Backend-LB-SG | HTTPS from Backend LB |
| Inbound | 22 | TCP | Bastion-Host-SG | SSH from Bastion |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

**6. Database-Security-Group**

| Direction | Port | Protocol | Source | Purpose |
|-----------|------|----------|--------|---------|
| Inbound | 3306 | TCP | Appservers-SG | MySQL from App Servers |
| Inbound | 3306 | TCP | Bastion-Host-SG | MySQL from Bastion |
| Outbound | All | All | 0.0.0.0/0 | All traffic |

### 6.2 Security Flow Diagram

```
Internet
    │
    ▼ [Ports 80, 443]
┌─────────────────────────┐
│  Frontend-LB-SG         │
└───────────┬─────────────┘
            │ [Ports 80, 443]
            ▼
┌─────────────────────────┐     ┌─────────────────────────┐
│  Webservers-SG          │◄────│  Bastion-Host-SG        │
└───────────┬─────────────┘     │  [Port 22]              │
            │ [Ports 80, 443]   └───────────┬─────────────┘
            ▼                               │
┌─────────────────────────┐                 │
│  Backend-LB-SG          │                 │
└───────────┬─────────────┘                 │
            │ [Ports 80, 443]               │
            ▼                               │ [Port 22]
┌─────────────────────────┐◄────────────────┤
│  Appservers-SG          │                 │
└───────────┬─────────────┘                 │
            │ [Port 3306]                   │ [Port 3306]
            ▼                               ▼
┌─────────────────────────────────────────────┐
│            Database-SG                       │
└─────────────────────────────────────────────┘
```

### 6.3 Additional Security Measures

| Security Feature | Implementation |
|------------------|----------------|
| **RDS Encryption** | Enabled at rest using AWS KMS |
| **S3 Block Public Access** | All public access blocked |
| **S3 Versioning** | Enabled for config recovery |
| **Private Subnets** | App and DB tiers isolated from internet |
| **NAT Gateway** | Controlled outbound internet access |
| **SSH Agent Forwarding** | Secure bastion host access pattern |
| **Input Sanitization** | PHP htmlentities() and mysqli_real_escape_string() |

---

## 7. High Availability & Fault Tolerance

### 7.1 Multi-AZ Architecture

Every component is deployed across two Availability Zones:

| Component | AZ-1a | AZ-1c | Failover Method |
|-----------|-------|-------|-----------------|
| NAT Gateway | ✅ | ✅ | Route table per AZ |
| Frontend ALB | ✅ | ✅ | Automatic |
| Web Servers | ✅ (min 1) | ✅ (min 1) | Auto Scaling |
| Backend ALB | ✅ | ✅ | Automatic |
| App Servers | ✅ (min 1) | ✅ (min 1) | Auto Scaling |
| RDS Primary | ✅ | - | Automatic failover |
| RDS Standby | - | ✅ | Promotes to primary |

### 7.2 Failure Scenarios

**Scenario 1: Single EC2 Instance Failure**
- ALB health check detects unhealthy instance
- Traffic automatically routed to healthy instances
- Auto Scaling launches replacement instance

**Scenario 2: Entire Availability Zone Failure**
- All traffic routes to remaining AZ
- Auto Scaling launches instances in surviving AZ
- NAT Gateway in surviving AZ handles outbound traffic

**Scenario 3: Database Primary Failure**
- RDS detects primary failure
- Automatic failover to standby (2-3 minutes)
- Endpoint DNS automatically updated
- No application code changes required

### 7.3 Health Checks

| Component | Health Check Type | Path | Interval |
|-----------|------------------|------|----------|
| Frontend ALB Target | HTTP | /OmariMailingApp.php | 30s |
| Backend ALB Target | HTTP | /OmariMailingApp.php | 30s |
| Route 53 | HTTP | /OmariMailingApp.php | 30s |
| Auto Scaling | EC2 + ELB | - | - |

---

## 8. Deployment Workflow

### 8.1 Phase 1: Network Foundation (Steps 1-4)

```
┌─────────────────────────────────────────────────────────────┐
│                    NETWORK SETUP                             │
├─────────────────────────────────────────────────────────────┤
│  1. Create VPC (10.0.0.0/16)                                │
│  2. Create 8 Subnets across 2 AZs                           │
│  3. Create 8 Route Tables                                   │
│  4. Associate Route Tables with Subnets                     │
│  5. Create Internet Gateway                                 │
│  6. Configure public route tables → IGW                     │
│  7. Create 2 NAT Gateways (with Elastic IPs)               │
│  8. Configure private route tables → NAT Gateways          │
└─────────────────────────────────────────────────────────────┘
```

### 8.2 Phase 2: Security Layer (Step 5)

```
┌─────────────────────────────────────────────────────────────┐
│                 SECURITY GROUPS                              │
├─────────────────────────────────────────────────────────────┤
│  1. Create Bastion-Host-Security-Group                      │
│  2. Create Frontend-LB-Security-Group                       │
│  3. Create Webservers-Security-Group                        │
│  4. Create Backend-LB-Security-Group                        │
│  5. Create Appservers-Security-Group                        │
│  6. Create Database-Security-Group                          │
└─────────────────────────────────────────────────────────────┘
```

### 8.3 Phase 3: Load Balancers (Step 6)

```
┌─────────────────────────────────────────────────────────────┐
│                 LOAD BALANCERS                               │
├─────────────────────────────────────────────────────────────┤
│  1. Create Frontend-LB-HTTP-TG (Target Group)               │
│  2. Create Prod-Frontend-LB (Internet-facing ALB)           │
│  3. Create Backend-LB-HTTP-TG (Target Group)                │
│  4. Create Prod-Backend-LB (Internal ALB)                   │
└─────────────────────────────────────────────────────────────┘
```

### 8.4 Phase 4: Database (Step 7)

```
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE                                  │
├─────────────────────────────────────────────────────────────┤
│  1. Create DB Subnet Group                                  │
│  2. Create RDS MySQL Instance (Multi-AZ)                    │
│  3. Note the Endpoint for dbinfo.inc                        │
└─────────────────────────────────────────────────────────────┘
```

### 8.5 Phase 5: Configuration Storage (Step 8)

```
┌─────────────────────────────────────────────────────────────┐
│                    S3 BUCKET                                 │
├─────────────────────────────────────────────────────────────┤
│  1. Create S3 Bucket                                        │
│  2. Update dbinfo.inc with RDS endpoint                     │
│  3. Update 000-default.conf with Backend LB DNS             │
│  4. Upload: dbinfo.inc, 000-default.conf,                   │
│             OmariMailingApp.php                           │
└─────────────────────────────────────────────────────────────┘
```

### 8.6 Phase 6: Compute Resources (Steps 9-11)

```
┌─────────────────────────────────────────────────────────────┐
│                 COMPUTE SETUP                                │
├─────────────────────────────────────────────────────────────┤
│  1. Create Bastion Host EC2 Instance                        │
│  2. Create IAM Role (EC2-AmazonS3ReadOnlyAccess)           │
│  3. Create Prod-Webservers-LT (Launch Template)            │
│  4. Create Prod-Appservers-LT (Launch Template)            │
│  5. Create prod-webservers-autoscaling-group               │
│  6. Create prod-appservers-autoscaling-group               │
└─────────────────────────────────────────────────────────────┘
```

### 8.7 Phase 7: DNS & Monitoring

```
┌─────────────────────────────────────────────────────────────┐
│                 DNS & MONITORING                             │
├─────────────────────────────────────────────────────────────┤
│  1. Create Route 53 CNAME Record → Frontend LB              │
│  2. Create Route 53 Health Check                            │
│  3. Configure SNS Topic for Alerts                          │
│  4. (Optional) Request ACM Certificate                      │
└─────────────────────────────────────────────────────────────┘
```

---

## 9. Cost Considerations

### 9.1 Monthly Cost Estimate (us-west-1)

| Service | Configuration | Estimated Monthly Cost |
|---------|---------------|----------------------|
| EC2 Instances | 5x t2.micro (1 Bastion + 2 Web + 2 App) | ~$42 |
| RDS MySQL | db.t2.micro Multi-AZ | ~$25 |
| NAT Gateway | 2x NAT Gateways + Data Processing | ~$65 |
| ALB | 2x Application Load Balancers | ~$32 |
| S3 | Minimal storage | ~$1 |
| Route 53 | Hosted Zone + Health Check | ~$2 |
| Data Transfer | Varies by traffic | Variable |
| **Total Estimate** | | **~$167/month** |

### 9.2 Cost Optimization Recommendations

1. **Development Environment**
   - Use single AZ deployment
   - Use single NAT Gateway
   - Use RDS Single-AZ

2. **Reserved Instances**
   - Purchase 1-year reserved instances for steady workloads
   - Potential savings: 30-40%

3. **Right-Sizing**
   - Monitor CPU/Memory utilization
   - Adjust instance types accordingly

4. **Auto Scaling**
   - Set aggressive scale-in policies
   - Use scheduled scaling for predictable patterns

---

## 10. Conclusion

### 10.1 Architecture Benefits

| Benefit | Implementation |
|---------|----------------|
| **High Availability** | Multi-AZ deployment across all tiers |
| **Scalability** | Auto Scaling for web and app tiers |
| **Security** | Layered security groups, private subnets |
| **Managed Services** | RDS handles database operations |
| **Fault Tolerance** | Automatic failover for RDS and ALB |
| **Separation of Concerns** | Distinct tiers for different functions |

### 10.2 Production Readiness Checklist

- [x] Multi-AZ deployment
- [x] Auto Scaling configured
- [x] Load balancing implemented
- [x] Security groups configured
- [x] Private subnets for sensitive tiers
- [x] Database encryption enabled
- [x] Automated backups enabled
- [x] Health checks configured
- [ ] HTTPS/SSL certificates (optional step)
- [ ] CloudWatch alarms configured
- [ ] Disaster recovery plan documented

### 10.3 Future Improvements

1. **Security Enhancements**
   - Implement AWS WAF for Frontend ALB
   - Use AWS Secrets Manager for database credentials
   - Enable VPC Flow Logs

2. **Performance Optimizations**
   - Add Amazon CloudFront CDN
   - Implement ElastiCache for session/query caching
   - Use RDS Read Replicas for read-heavy workloads

3. **Operational Improvements**
   - Implement CI/CD with AWS CodePipeline
   - Add comprehensive CloudWatch dashboards
   - Configure AWS Backup for centralized backups

---

**Report Prepared By:** AI Assistant  
**Date:** December 2025  
**Version:** 1.0

