ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli-alpine

RUN apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY src/ src/
COPY benchmark.php .

RUN mkdir -p /results

ENV RESULTS_DIR=/results

ENTRYPOINT ["php", "benchmark.php"]
