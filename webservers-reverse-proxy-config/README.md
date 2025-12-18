# Omari Mailing Service - Web Server Configuration

## Step 1: Execute the `web-automation.sh` script to install Apache2
- Confirm that the service is started and you can access from the browser
- The script will automatically install Apache2 and required modules

## Step 2: Configure and Setup The Apache2 Reverse Proxy to Point at The Internal Load Balancer

### Important Notes:
- Before applying any change on the `sites-available` config files, take a Backup for Recovery purpose 
- Before running the below commands, **EDIT** the Load Balancer DNS in the Reverse Proxy File `000-default.conf` with your LoadBalancer DNS 

### Manual Configuration Steps (if not using automation script):

```bash
ls /etc/apache2
```
```bash
ls /etc/apache2/sites-enabled
```
```bash
sudo rm /etc/apache2/sites-enabled/000-default.conf
```

- You can create the `000-default.conf` directly in the Web Server using any text editor like `vi`:
```bash
# The config should contain:
# ProxyPass / http://YOUR_BACKEND_LB_DNS/OmariMailingApp.php/
```

## Enable Apache2 Proxy Modules

We need to enable two Apache2 modules using `a2enmod` for the proxy to work:

1. Enable Module: `proxy`
```bash
sudo a2enmod proxy
```

2. Enable Module: `proxy_http`
```bash
sudo a2enmod proxy_http
```

3. Restart Apache:
```bash
sudo systemctl restart apache2
```

### ✅ Congratulations! Your Omari Web Server Proxy Has Been Configured

