FROM php:8.1-apache

# Accept build arguments for user/group IDs and Linux distribution
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG LINUX_DIST
ARG OS_TYPE

# Install system dependencies based on distribution
RUN if [ "$OS_TYPE" = "linux" ]; then \
        if [ "$LINUX_DIST" = "ubuntu" ] || [ "$LINUX_DIST" = "kali" ]; then \
            apt-get update && apt-get install -y \
            libpng-dev \
            libjpeg-dev \
            libfreetype6-dev \
            zip \
            unzip \
            mariadb-client; \
        elif [ "$LINUX_DIST" = "redhat" ] || [ "$LINUX_DIST" = "centos" ] || [ "$LINUX_DIST" = "rocky" ]; then \
            dnf update -y && dnf install -y \
            libpng-devel \
            libjpeg-devel \
            freetype-devel \
            zip \
            unzip \
            mariadb \
            httpd \
            mod_ssl && \
            dnf clean all; \
        fi \
    else \
        apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        mariadb-client; \
    fi

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli

# Enable Apache modules and configure based on distribution
RUN if [ "$OS_TYPE" = "linux" ] && ( [ "$LINUX_DIST" = "redhat" ] || [ "$LINUX_DIST" = "centos" ] || [ "$LINUX_DIST" = "rocky" ] ); then \
        echo "ServerName localhost" > /etc/apache2/conf-available/server-name.conf && \
        echo "LoadModule rewrite_module modules/mod_rewrite.so" > /etc/apache2/mods-available/rewrite.load && \
        echo "LoadModule headers_module modules/mod_headers.so" > /etc/apache2/mods-available/headers.load && \
        ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ && \
        ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/; \
    else \
        a2enmod rewrite headers && \
        echo "ServerName localhost" >> /etc/apache2/apache2.conf; \
    fi

# Configure PHP
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini

# Create required directories
RUN mkdir -p /var/www/html/image/share /var/www/html/logs

# Update user/group IDs (use www-data by default)
RUN if [ "$OS_TYPE" = "linux" ] && ( [ "$LINUX_DIST" = "redhat" ] || [ "$LINUX_DIST" = "centos" ] || [ "$LINUX_DIST" = "rocky" ] ); then \
        groupmod -o -g ${GROUP_ID} apache || true && \
        usermod -o -u ${USER_ID} -g ${GROUP_ID} apache || true && \
        chown -R apache:apache /var/www/html; \
    else \
        groupmod -o -g ${GROUP_ID} www-data || true && \
        usermod -o -u ${USER_ID} -g ${GROUP_ID} www-data || true && \
        chown -R www-data:www-data /var/www/html; \
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
