FROM php:8.2-apache

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Copy application files
COPY . /var/www/html/VAR-Cars

# Create redirect index
RUN echo '<?php header("Location: /VAR-Cars/public/index.php"); exit;' > /var/www/html/index.php

# Create startup script that fixes MPM modules before Apache starts
RUN echo '#!/bin/bash\n\
set -e\n\
# Disable all MPM modules\n\
a2dismod mpm_event mpm_worker 2>/dev/null || true\n\
# Enable only mpm_prefork (required for mod_php)\n\
a2enmod mpm_prefork\n\
# Update port configuration\n\
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf\n\
sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf\n\
# Start Apache\n\
exec apache2-foreground' > /usr/local/bin/start-apache.sh

RUN chmod +x /usr/local/bin/start-apache.sh

# Expose port
EXPOSE 80

# Use the startup script
CMD ["/usr/local/bin/start-apache.sh"]
