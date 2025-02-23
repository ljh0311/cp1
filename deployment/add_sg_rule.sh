#!/bin/bash

# Get EC2 security group ID
EC2_SG_ID=$(aws ec2 describe-security-groups --filters Name=group-name,Values=launch-wizard-1 --query 'SecurityGroups[0].GroupId' --output text)

# Get RDS security group ID
RDS_SG_ID=$(aws ec2 describe-security-groups --filters Name=group-name,Values=default --query 'SecurityGroups[0].GroupId' --output text)

echo "Adding inbound rule to allow MySQL access from EC2 security group..."
aws ec2 authorize-security-group-ingress \
    --group-id $RDS_SG_ID \
    --protocol tcp \
    --port 3306 \
    --source-group $EC2_SG_ID

echo "Security group rule added successfully!" 