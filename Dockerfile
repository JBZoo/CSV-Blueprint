#
# JBZoo Toolbox - Csv-Blueprint.
#
# This file is part of the JBZoo Toolbox project.
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @license    MIT
# @copyright  Copyright (C) JBZoo.com, All rights reserved.
# @see        https://github.com/JBZoo/Csv-Blueprint
#

FROM alpine:latest as preparatory
RUN apk add --no-cache make git
WORKDIR /tmp
COPY . /tmp
RUN make build-version

########################################################################################
FROM php:8.3-cli-alpine

# Install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions opcache @composer

# Install application
# run `make build-version` before!
WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . /app
COPY --from=preparatory /tmp/.version /app/.version
RUN composer install --no-dev                       \
                        --classmap-authoritative    \
                        --no-progress               \
                        --no-suggest                \
                        --optimize-autoloader       \
    && rm -rf ./.git                                \
    && composer clear-cache                         \
    && chmod +x ./csv-blueprint

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ./docker/php.ini /usr/local/etc/php/conf.d/docker-z99-php.ini

# Warmup caches
RUN php ./docker/build-preloader.php && php ./docker/preload.php

# Quick test
RUN time ./csv-blueprint --version --ansi \
    && time ./csv-blueprint validate:csv --help --ansi

#RUN time php ./docker/random-csv.php             \
#    && echo "opcache.preload=./docker/preload.php" >> /usr/local/etc/php/conf.d/docker-z99-php.ini
#    && time ./csv-blueprint validate:csv -h      \
#    && time ./csv-blueprint validate:schema      \
#      --schema=./schema-examples/*.yml           \
#      --schema=./schema-examples/*.php           \
#      --schema=./schema-examples/*.json -vvv     \
#    && echo "Warm up is ready!"                  \

#RUN time php ./docker/random-csv.php             \
#    && time ./csv-blueprint validate:csv         \
#      --schema=./schema-examples/full.yml        \
#      --csv=./docker/random_data.csv             \
#      --apply-all=yes                            \
#      --report=text --mute-errors > /dev/null    \
#    && echo "Tests are ready!"                   \
#    && rm ./docker/random_data.csv               \
#    && du -sh ./docker

ENTRYPOINT ["/app/csv-blueprint"]
