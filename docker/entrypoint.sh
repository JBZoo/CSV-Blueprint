#!/bin/sh

# Check for the presence of the "--parallel" option in the command line arguments
# If the option is present, disable the opcache for the script execution
# This is necessary because the opcache is not thread-safe and will cause segfaults
if [[ " $* " =~ " --parallel" ]] || [[ " $* " =~ " --parallel=" ]] || [[ " $* " =~ " --parallel " ]]; then
    php -d opcache.enable_cli=0 /app/csv-blueprint.php "$@"
else
    php /app/csv-blueprint.php "$@"
fi
