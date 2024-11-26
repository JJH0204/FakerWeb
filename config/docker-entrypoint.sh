#!/bin/bash
set -e

# Detect Linux distribution
LINUX_DIST=$(if [ -f "/etc/os-release" ]; then \
    if grep -qi "ubuntu" /etc/os-release; then \
        echo "ubuntu"; \
    elif grep -qi "rhel\|centos\|fedora\|rocky" /etc/os-release; then \
        echo "redhat"; \
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

# Create required directories
mkdir -p /var/www/html/image/share
mkdir -p /var/www/html/logs

# Set permissions
chown -R $WEB_USER:$WEB_GROUP /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/html/image/share
chmod -R 775 /var/www/html/logs

# Configure Apache based on OS/distribution
if [ "$LINUX_DIST" = "redhat" ]; then
    # RHEL/CentOS/Rocky specific configuration
    exec httpd-foreground
else
    # Default Apache configuration (Ubuntu/Debian based)
    exec apache2-foreground
fi
