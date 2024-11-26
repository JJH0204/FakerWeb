FROM php:8.1-apache

# Accept build arguments for user/group IDs and Linux distribution
ARG USER_ID=1000
ARG GROUP_ID=1000
ARG LINUX_DIST
ARG OS_TYPE

# Install system dependencies based on distribution
RUN if [ "$OS_TYPE" = "linux" ]; then \
        if [ "$LINUX_DIST" = "ubuntu" ] || [ "$LINUX_DIST" = "kali" ]; then \
            apt-get update --allow-releaseinfo-change || true && \
            apt-get install -y \
            libpng-dev \
            libjpeg-dev \
            libfreetype6-dev \
            zip \
            unzip \
            gosu \
            dos2unix \
            mariadb-client || default-mysql-client; \
        elif [ "$LINUX_DIST" = "redhat" ] || [ "$LINUX_DIST" = "centos" ] || [ "$LINUX_DIST" = "rocky" ]; then \
            dnf update -y && dnf install -y \
            libpng-devel \
            libjpeg-devel \
            freetype-devel \
            zip \
            unzip \
            mariadb \
            httpd \
            dos2unix \
            mod_ssl && \
            dnf clean all && \
            curl -o /usr/local/bin/gosu -SL "https://github.com/tianon/gosu/releases/download/1.14/gosu-amd64" && \
            chmod +x /usr/local/bin/gosu; \
        fi \
    else \
        apt-get update --allow-releaseinfo-change || true && \
        apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zip \
        unzip \
        gosu \
        dos2unix \
        mariadb-client || default-mysql-client; \
    fi

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite
RUN a2enmod headers

# Configure Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's/^Listen 80/Listen 0.0.0.0:80/' /etc/apache2/ports.conf

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

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY config/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh && \
    dos2unix /usr/local/bin/docker-entrypoint.sh

# Expose port
EXPOSE 80

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
