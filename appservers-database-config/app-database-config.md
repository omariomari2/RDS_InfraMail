# Omari Mailing Service - Database Configuration

## Create a Database config file in the application servers 

```bash
vi /var/www/inc/dbinfo.inc
```

## Copy and Paste the Below Data in the file after updating with your values

```php
<?php

define('DB_SERVER', 'YOUR_RDS_ENDPOINT_HERE');
define('DB_USERNAME', 'admin');
define('DB_PASSWORD', 'YOUR_PASSWORD_HERE');
define('DB_DATABASE', 'omaridatabase');

?>
```

## Configuration Parameters:

| Parameter | Description | Example |
|-----------|-------------|---------|
| `DB_SERVER` | RDS MySQL Endpoint (without port) | `omari-database.xxx.us-west-1.rds.amazonaws.com` |
| `DB_USERNAME` | Database master username | `admin` |
| `DB_PASSWORD` | Database master password | `your_secure_password` |
| `DB_DATABASE` | Initial database name | `omaridatabase` |

## Testing Database Connection

From the Bastion Host, test the connection:
```bash
mysql -h YOUR_RDS_ENDPOINT -u admin -p
```