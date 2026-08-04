ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli-alpine

RUN apk add --no-cache unzip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# No composer.lock: each PHP version resolves the newest libraries it supports.
# symfony/validator 8.x needs PHP >= 8.4.1 and respect/validation 3.x needs PHP >= 8.5,
# so 8.2/8.3 fall back to Symfony 7.4 / Respect 2.x. benchmark.php records what it got.
COPY composer.json ./
RUN composer update --no-dev --optimize-autoloader --no-interaction

COPY src/ src/
COPY benchmark.php compare.php ./

RUN mkdir -p /results

ENV RESULTS_DIR=/results

ENTRYPOINT ["php", "benchmark.php"]
