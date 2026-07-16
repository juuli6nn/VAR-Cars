FROM php:8.2-cli

RUN docker-php-ext-install mysqli

COPY . /var/www/html/VAR-Cars

RUN echo '<?php header("Location: /VAR-Cars/public/index.php"); exit;' > /var/www/html/index.php

ENV PHP_CLI_SERVER_WORKERS=8

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /var/www/html"]
