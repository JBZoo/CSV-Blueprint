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
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . /app
COPY --from=preparatory /tmp/.version /app/.version
RUN cd /app                                         \
    && composer install --no-dev                    \
                        --classmap-authoritative    \
                        --no-progress               \
                        --no-suggest                \
                        --optimize-autoloader       \
    && rm -rf /app/.git                             \
    && composer clear-cache                         \
    && chmod +x /app/csv-blueprint

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ./docker/php.ini /usr/local/etc/php/conf.d/docker-z99-php.ini

# Prepare opcode caches
RUN php /app/docker/build-preloader.php \
    && php /app/docker/preload.php
#    && echo "opcache.preload=/app/docker/preload.php" >> /usr/local/etc/php/conf.d/docker-z99-php.ini

# Test and warm up caches
RUN time /app/csv-blueprint validate:csv -h       \
    && time /app/csv-blueprint validate:schema    \
      --schema=/app/schema-examples/*.yml         \
      --schema=/app/schema-examples/*.php         \
      --schema=/app/schema-examples/*.json -vvv

RUN du -sh /app/docker

ENTRYPOINT ["/app/csv-blueprint"]
