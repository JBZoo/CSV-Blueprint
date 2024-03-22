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
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
#COPY resources/php.ini /usr/local/etc/php/php.ini

# Install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions opcache @composer

# Install application
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . /app
RUN chmod +x /app/csv-blueprint

RUN cd /app                                         \
    && composer update --no-dev                     \
                        --optimize-autoloader       \
                        --classmap-authoritative    \
                        --no-progress               \
    && composer clear-cache                         \
    && cat "$PHP_INI_DIR/php.ini"        \
    && php -i                        \
    && /app/csv-blueprint -h


ENTRYPOINT ["/app/csv-blueprint"]
