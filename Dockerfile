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

FROM php:8.3-cli-alpine

# Install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions opcache @composer

# Install application
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY . /app
RUN cd /app                                         \
    && composer install --no-dev                    \
                        --no-progress               \
    && composer clear-cache                         \
    && chmod +x /app/csv-blueprint

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini /usr/local/etc/php/conf.d/docker-z99-php.ini

# Test and warmup opcache
RUN /app/csv-blueprint validate:csv -h

ENTRYPOINT ["/app/csv-blueprint"]
