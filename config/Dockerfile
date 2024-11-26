FROM php:8.1-apache

# Accept build arguments for user/group IDs and Linux distribution
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG LINUX_DIST=ubuntu

# Install system dependencies based on distribution
RUN if [ "$LINUX_DIST" = "ubuntu" ]; then \
        apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        default-mysql-client; \
    elif [ "$LINUX_DIST" = "redhat" ]; then \
        dnf install -y epel-release && \
        dnf config-manager --set-enabled crb && \
        dnf install -y \
        libpng-devel \
        libjpeg-devel \
        freetype-devel \
        zip \
        unzip \
        mysql && \
        dnf clean all; \
    else \
        apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        default-mysql-client; \
    fi

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite headers

# Set up Apache configuration based on distribution
RUN if [ "$LINUX_DIST" = "redhat" ]; then \
        echo "ServerName localhost" > /etc/httpd/conf.d/server-name.conf && \
        sed -i 's/User apache/User ${WEB_USER}/g' /etc/httpd/conf/httpd.conf && \
        sed -i 's/Group apache/Group ${WEB_GROUP}/g' /etc/httpd/conf/httpd.conf; \
    else \
        echo "ServerName localhost" >> /etc/apache2/apache2.conf; \
    fi

# Configure PHP
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Create required directories
RUN mkdir -p /var/www/html/image/share /var/www/html/logs

# Update www-data user/group IDs
RUN if [ "$LINUX_DIST" = "redhat" ]; then \
        groupmod -o -g ${GROUP_ID} apache || true && \
        usermod -o -u ${USER_ID} -g ${GROUP_ID} apache || true; \
    else \
        groupmod -o -g ${GROUP_ID} www-data || true && \
        usermod -o -u ${USER_ID} -g ${GROUP_ID} www-data || true; \
    fi

# Copy entrypoint script
COPY config/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Expose port
EXPOSE 80

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
