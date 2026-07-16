FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Fix MPM conflict by removing conflicting module configs and load files
RUN rm -f /etc/apache2/mods-enabled/mpm_event.conf \
          /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_worker.conf \
          /etc/apache2/mods-enabled/mpm_worker.load && \
    a2enmod mpm_prefork && \
    a2enmod rewrite

# Set ServerName to suppress warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy application files
COPY . /var/www/html/VAR-Cars

# Create redirect index
RUN echo '<?php header("Location: /VAR-Cars/public/index.php"); exit;' > /var/www/html/index.php

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80

# Configure Apache ports at runtime and start
CMD sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf && \
    sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf && \
    apache2-foreground
