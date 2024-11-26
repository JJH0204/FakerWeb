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

# Set web user and group based on distribution
if [ "$LINUX_DIST" = "ubuntu" ]; then
    WEB_USER="www-data"
    WEB_GROUP="www-data"
else
    WEB_USER="apache"
    WEB_GROUP="apache"
fi

echo "Using web user: $WEB_USER"
echo "Using web group: $WEB_GROUP"

# Create required directories
mkdir -p /var/www/html/image/share
mkdir -p /var/www/html/logs

# Set permissions
chown -R $WEB_USER:$WEB_GROUP /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
chmod -R 775 /var/www/html/image/share
chmod -R 775 /var/www/html/logs

# Start Apache based on distribution
if [ "$LINUX_DIST" = "ubuntu" ]; then
    exec apache2-foreground
else
    exec httpd-foreground
fi
