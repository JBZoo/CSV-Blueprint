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

# Check for the presence of the "--parallel" option in the command line arguments
# If the option is present, disable the opcache for the script execution
# This is necessary because the opcache is not thread-safe and will cause segfaults.
# We have to debug the segfaults and fix them, but for now, this is a workaround.
if [[ " $* " =~ " --parallel" ]] || [[ " $* " =~ " --parallel=" ]] || [[ " $* " =~ " --parallel " ]]; then
    php -d opcache.enable_cli=0 /app/csv-blueprint.php "$@"
else
    php /app/csv-blueprint.php "$@"
fi
