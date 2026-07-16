FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Nuclear option: Remove MPM module loading from apache2.conf entirely
RUN sed -i '/LoadModule mpm_/d' /etc/apache2/apache2.conf

# Manually load only mpm_prefork
RUN echo "LoadModule mpm_prefork_module /usr/lib/apache2/modules/mod_mpm_prefork.so" >> /etc/apache2/apache2.conf

# Enable rewrite
RUN a2enmod rewrite

# Set ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/VAR-Cars

# Create redirect index
RUN echo '<?php header("Location: /VAR-Cars/public/index.php"); exit;' > /var/www/html/index.php

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Start Apache
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf && \
    sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf && \
    apache2-foreground
