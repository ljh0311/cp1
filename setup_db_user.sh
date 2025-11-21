#!/bin/bash

echo "Connecting to RDS to create user and set permissions..."

# Connect to MySQL and execute commands
mysql -h <URL> -u <usernane> -p'<PW>!' << EOF
CREATE DATABASE IF NOT EXISTS <DBNAME>;
GRANT ALL PRIVILEGES ON  <DBNAME>.* TO '<usernane>'@'%';
FLUSH PRIVILEGES;

SHOW GRANTS FOR '<usernane>'@'%';
EOF

echo "Database user setup completed!" 
