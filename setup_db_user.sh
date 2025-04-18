#!/bin/bash

echo "Connecting to RDS to create user and set permissions..."

# Connect to MySQL and execute commands
mysql -h cloudbookdb.czsa24cac7y5.us-east-1.rds.amazonaws.com -u admin -p'BookStore123!' << EOF
CREATE DATABASE IF NOT EXISTS MyBookDB;
GRANT ALL PRIVILEGES ON MyBookDB.* TO 'admin'@'%';
FLUSH PRIVILEGES;

SHOW GRANTS FOR 'admin'@'%';
EOF

echo "Database user setup completed!" 