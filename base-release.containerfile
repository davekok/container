FROM php:8.1.2
RUN docker-php-ext-install pcntl
WORKDIR /app
ENTRYPOINT ["/usr/local/bin/php", "/app/vendor/bin/run"]
