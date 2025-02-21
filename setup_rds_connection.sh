#!/bin/bash

# Install MySQL client if not already installed
if ! command -v mysql &> /dev/null; then
    echo "Installing MySQL client..."
    sudo yum install -y mysql
fi

# Get EC2 instance security group ID
EC2_SG_ID=$(aws ec2 describe-security-groups --filters Name=group-name,Values=launch-wizard-1 --query 'SecurityGroups[0].GroupId' --output text)

# Get RDS security group ID
RDS_SG_ID=$(aws ec2 describe-security-groups --filters Name=group-name,Values=bookstore-db-sg --query 'SecurityGroups[0].GroupId' --output text)

# Add inbound rule to RDS security group to allow traffic from EC2
aws ec2 authorize-security-group-ingress \
    --group-id $RDS_SG_ID \
    --protocol tcp \
    --port 3306 \
    --source-group $EC2_SG_ID

echo "Security group rules updated!"

# Test MySQL connection
echo "Testing connection to RDS..."
mysql -h book-db.czsa24cac7y5.us-east-1.rds.amazonaws.com -u bookadmin -p -e "SELECT NOW();"

echo "If you see a timestamp above, the connection is successful!" 