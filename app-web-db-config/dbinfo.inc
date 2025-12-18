<?php
/**
 * Omari Mailing Service - Database Configuration
 * 
 * Update these values with your AWS RDS MySQL instance details
 */

define('DB_SERVER', 'YOUR_RDS_ENDPOINT_HERE');      // Example: omari-db.xxx.us-west-1.rds.amazonaws.com
define('DB_USERNAME', 'admin');                      // Your RDS master username
define('DB_PASSWORD', 'YOUR_SECURE_PASSWORD');       // Your RDS master password
define('DB_DATABASE', 'omaridatabase');              // Your initial database name

?>