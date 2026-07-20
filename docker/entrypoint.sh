#!/usr/bin/env sh

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

# Check for the presence of the "--parallel" option in the command line arguments.
# If present, disable opcache for this run: opcache is not thread-safe and can segfault
# together with ext-parallel. Removing this guard bought no measurable speedup, so it is
# kept until a multi-day/varied-topology soak test proves it safe to drop.
if [ "${*#*--parallel}" != "$*" ]; then
    php -d opcache.enable_cli=0 /app/csv-blueprint.php "$@"
else
    php /app/csv-blueprint.php "$@"
fi
