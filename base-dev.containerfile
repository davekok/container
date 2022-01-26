FROM php:8.1.2
RUN docker-php-ext-install pcntl && pecl install pcov && docker-php-ext-enable pcov
WORKDIR /app
ENTRYPOINT ["/usr/local/bin/php", "/app/vendor/bin/run"]
