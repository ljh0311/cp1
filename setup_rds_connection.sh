#!/bin/bash

# Install MySQL client if not already installed
if ! command -v mysql &> /dev/null; then
    echo "Installing MySQL client..."
    sudo yum install -y mysql
fi

# Get EC2 instance security group ID
EC2_SG_ID=$(aws ec2 describe-security-groups --filters Name=group-name,Values=launch-wizard-1 --query 'SecurityGroups[0].GroupId' --output text)

echo "EC2 Security Group ID: $EC2_SG_ID"

# Test MySQL connection
echo "Testing connection to RDS..."
mysql -h cloudbookdb.czsa24cac7y5.us-east-1.rds.amazonaws.com -u admin -p'Admin123' -e "SELECT NOW(); SHOW DATABASES;"

echo "If you see a timestamp above, the connection is successful!"

# Additional connection test using netcat
echo "Testing port connectivity..."
nc -zv cloudbookdb.czsa24cac7y5.us-east-1.rds.amazonaws.com 3306 