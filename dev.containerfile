FROM composer AS composer
COPY composer.* /app
RUN composer install --ignore-platform-reqs

FROM davekok/wiring:base-dev
COPY --from=composer /app/vendor /app/vendor
COPY run /app/vendor/bin/run
COPY src /app/src
COPY tests /app/tests
