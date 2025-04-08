# AWS Deployment Guide

This guide provides step-by-step instructions for deploying the Online Bookstore E-Commerce Platform on AWS infrastructure.

## Architecture Overview

```
┌─────────────────┐     ┌──────────────┐
│   AWS EC2       │     │   AWS RDS    │
│  (Web Server)   │────▶│   (MySQL)    │
└─────────────────┘     └──────────────┘
        ▲
        │
        │
   ┌────┴─────┐
   │  Users   │
   └──────────┘
```

## Prerequisites

1. AWS Account with appropriate permissions
2. AWS CLI installed and configured
3. SSH client for connecting to EC2
4. MySQL client for database operations

## Step 1: Database Setup (AWS RDS)

1. Create RDS Instance:
   ```bash
   aws rds create-db-instance \
     --db-instance-identifier bookstore-db \
     --db-instance-class db.t3.micro \
     --engine mysql \
     --master-username admin \
     --master-user-password <your-secure-password> \
     --allocated-storage 20
   ```

2. Configure Security Group:
   ```bash
   # Create security group for RDS
   aws ec2 create-security-group \
     --group-name bookstore-rds-sg \
     --description "Security group for Bookstore RDS"

   # Add inbound rule for MySQL (3306)
   aws ec2 authorize-security-group-ingress \
     --group-name bookstore-rds-sg \
     --protocol tcp \
     --port 3306 \
     --source-group <your-ec2-security-group>
   ```

3. Initialize Database:
   ```bash
   mysql -h <your-rds-endpoint> -u admin -p < database/schema.sql
   ```

## Step 2: EC2 Instance Setup

1. Launch EC2 Instance:
   ```bash
   aws ec2 run-instances \
     --image-id ami-0c55b159cbfafe1f0 \
     --instance-type t2.micro \
     --key-name <your-key-pair> \
     --security-group-ids <your-security-group> \
     --user-data file://deployment/ec2-user-data.sh
   ```

2. Configure Security Group:
   ```bash
   # Create security group for EC2
   aws ec2 create-security-group \
     --group-name bookstore-ec2-sg \
     --description "Security group for Bookstore EC2"

   # Add inbound rules
   aws ec2 authorize-security-group-ingress \
     --group-name bookstore-ec2-sg \
     --protocol tcp \
     --port 80 \
     --cidr 0.0.0.0/0

   aws ec2 authorize-security-group-ingress \
     --group-name bookstore-ec2-sg \
     --protocol tcp \
     --port 443 \
     --cidr 0.0.0.0/0
   ```

## Step 3: Application Deployment

1. Connect to EC2:
   ```bash
   ssh -i <your-key-pair.pem> ec2-user@<your-ec2-ip>
   ```

2. Clone Repository:
   ```bash
   git clone <your-repository> /var/www/html/bookstore
   cd /var/www/html/bookstore
   ```

3. Configure Application:
   ```bash
   # Copy configuration files
   cp config.example.php config.php

   # Update database configuration
   sed -i "s/DB_HOST=.*/DB_HOST=<your-rds-endpoint>/" config.php
   sed -i "s/DB_USER=.*/DB_USER=admin/" config.php
   sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=<your-password>/" config.php
   ```

4. Set Permissions:
   ```bash
   sudo chown -R apache:apache /var/www/html/bookstore
   sudo chmod -R 755 /var/www/html/bookstore
   sudo chmod -R 777 /var/www/html/bookstore/uploads
   sudo chmod -R 777 /var/www/html/bookstore/sessions
   ```

## Step 4: SSL Configuration

1. Install Certbot:
   ```bash
   sudo amazon-linux-extras install epel
   sudo yum install certbot python-certbot-apache
   ```

2. Obtain SSL Certificate:
   ```bash
   sudo certbot --apache -d yourdomain.com
   ```

## Step 5: Monitoring Setup

1. Configure CloudWatch:
   ```bash
   # Install CloudWatch agent
   sudo yum install amazon-cloudwatch-agent

   # Configure CloudWatch
   sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-config-wizard
   ```

2. Enable Log Monitoring:
   ```bash
   # Start CloudWatch agent
   sudo systemctl start amazon-cloudwatch-agent
   sudo systemctl enable amazon-cloudwatch-agent
   ```

## Maintenance Tasks

### Database Backups
```bash
# Create automated snapshot
aws rds create-db-snapshot \
  --db-instance-identifier bookstore-db \
  --db-snapshot-identifier bookstore-backup-$(date +%Y%m%d)
```

### Log Rotation
```bash
# Configure logrotate
sudo nano /etc/logrotate.d/bookstore

/var/www/html/bookstore/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
    create 640 apache apache
    sharedscripts
    postrotate
        /bin/systemctl reload httpd.service > /dev/null 2>/dev/null || true
    endscript
}
```

## Troubleshooting

1. Check Apache logs:
   ```bash
   sudo tail -f /var/log/httpd/error_log
   ```

2. Check Application logs:
   ```bash
   tail -f /var/www/html/bookstore/logs/error.log
   ```

3. Test Database Connection:
   ```bash
   mysql -h <rds-endpoint> -u admin -p -e "SELECT 1;"
   ```

## Security Best Practices

1. Regular Updates:
   ```bash
   sudo yum update -y
   ```

2. Firewall Configuration:
   ```bash
   sudo systemctl start firewalld
   sudo firewall-cmd --permanent --add-service=http
   sudo firewall-cmd --permanent --add-service=https
   sudo firewall-cmd --reload
   ```

3. File Permissions:
   ```bash
   find /var/www/html/bookstore -type f -exec chmod 644 {} \;
   find /var/www/html/bookstore -type d -exec chmod 755 {} \;
   ```

## Scaling Considerations

1. Enable Auto Scaling:
   ```bash
   # Create launch template
   aws ec2 create-launch-template \
     --launch-template-name bookstore-template \
     --version-description v1 \
     --launch-template-data file://launch-template.json

   # Create Auto Scaling group
   aws autoscaling create-auto-scaling-group \
     --auto-scaling-group-name bookstore-asg \
     --launch-template LaunchTemplateName=bookstore-template \
     --min-size 1 \
     --max-size 3 \
     --desired-capacity 1 \
     --vpc-zone-identifier "subnet-xxx,subnet-yyy"
   ```

2. Configure RDS Read Replicas:
   ```bash
   aws rds create-db-instance-read-replica \
     --db-instance-identifier bookstore-replica \
     --source-db-instance-identifier bookstore-db
   ```

## Backup and Recovery

1. Database Backup:
   ```bash
   mysqldump -h <rds-endpoint> -u admin -p --all-databases > backup.sql
   ```

2. Application Backup:
   ```bash
   tar -czf /backup/bookstore-$(date +%Y%m%d).tar.gz /var/www/html/bookstore
   ```

## Monitoring and Alerts

1. Set up CloudWatch Alarms:
   ```bash
   aws cloudwatch put-metric-alarm \
     --alarm-name CPU-Usage-Alarm \
     --alarm-description "CPU usage exceeds 80%" \
     --metric-name CPUUtilization \
     --namespace AWS/EC2 \
     --statistic Average \
     --period 300 \
     --threshold 80 \
     --comparison-operator GreaterThanThreshold \
     --evaluation-periods 2 \
     --alarm-actions <your-sns-topic-arn>
   ```

For support and troubleshooting, contact:
- DevOps Team: devops@yourdomain.com
- Emergency Contact: +1-XXX-XXX-XXXX 