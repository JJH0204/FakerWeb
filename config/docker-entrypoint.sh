#!/bin/bash
set -e

# Detect Linux distribution
LINUX_DIST=$(if [ -f "/etc/os-release" ]; then \
    if grep -qi "ubuntu" /etc/os-release; then \
        echo "ubuntu"; \
    elif grep -qi "rhel" /etc/os-release; then \
        echo "redhat"; \
    elif grep -qi "centos" /etc/os-release; then \
        echo "centos"; \
    elif grep -qi "rocky" /etc/os-release; then \
        echo "rocky"; \
    else \
        echo "unknown"; \
    fi; \
else \
    echo "unknown"; \
fi)

echo "Setting up for distribution: $LINUX_DIST"

# Set default web user and group
WEB_USER="${WEB_USER:-www-data}"
WEB_GROUP="${WEB_GROUP:-www-data}"

echo "Starting service with web user: $WEB_USER"
echo "Starting service with web group: $WEB_GROUP"

# Create required directories with root
mkdir -p /var/www/html/image/share
mkdir -p /var/www/html/logs

# Set base permissions
chown -R $WEB_USER:$WEB_GROUP /var/www/html
find /var/www/html -type d -exec chmod 775 {} \;
find /var/www/html -type f -exec chmod 664 {} \;

# Set specific directory permissions
chmod -R 775 /var/www/html/image/share
chmod -R 775 /var/www/html/logs

# Start Apache with the appropriate user
if [ "$LINUX_DIST" = "redhat" ] || [ "$LINUX_DIST" = "centos" ] || [ "$LINUX_DIST" = "rocky" ]; then
    # RHEL/CentOS/Rocky specific configuration
    exec gosu apache httpd-foreground
else
    # Ubuntu/Debian specific configuration
    exec apache2-foreground
fi
