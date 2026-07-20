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

# Note: the historical "disable opcache under --parallel" workaround (opcache segfaults with
# ext-parallel) was removed after verifying on PHP 8.5 ZTS that opcache + parallel runs are
# stable and produce identical results.
php /app/csv-blueprint.php "$@"
