#!/bin/bash

# Create sessions directory if it doesn't exist
mkdir -p sessions

# Set proper ownership (assuming apache/www-data is the web server user)
if [ -f /etc/redhat-release ]; then
    # For Amazon Linux/RHEL/CentOS
    chown apache:apache sessions
else
    # For Ubuntu/Debian
    chown www-data:www-data sessions
fi

# Set proper permissions (only web server can read/write)
chmod 733 sessions

# Create logs directory if it doesn't exist
mkdir -p logs

# Set proper ownership for logs
if [ -f /etc/redhat-release ]; then
    chown apache:apache logs
else
    chown www-data:www-data logs
fi

# Set proper permissions for logs
chmod 755 logs

echo "Permissions have been set up correctly."

# Display current permissions
ls -la sessions/
ls -la logs/ 