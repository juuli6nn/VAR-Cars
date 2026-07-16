FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy application files
COPY . /var/www/html/VAR-Cars

# Create redirect index
RUN echo '<?php header("Location: /VAR-Cars/public/index.php"); exit;' > /var/www/html/index.php

# Expose port
EXPOSE 80

# Disable conflicting MPM modules at runtime and start Apache
CMD ["sh", "-c", "a2dismod mpm_event mpm_worker 2>/dev/null || true && a2enmod mpm_prefork && sed -i \"s/Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -i \"s/:80/:${PORT:-80}/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
