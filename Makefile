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


ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif

OUTPUT ?= table

update: ##@Project Install/Update all 3rd party dependencies
	$(call title,"Install/Update all 3rd party dependencies")
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS)


test-all: ##@Project Run all project tests at once
	@make test
	@make codestyle


demo-valid: ##@Project Run demo valid CSV
	$(call title,"Demo - Valid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_valid.yml


demo-invalid: ##@Project Run demo invalid CSV
	$(call title,"Demo - Invalid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_invalid.yml  \
       --output=github

demo-github: ##@Project Run demo invalid CSV
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_invalid.yml  \
       --output=$(OUTPUT)                         \
       --ansi


demo: ##@Project Run all demo commands
	@make demo-valid
	@make demo-invalid
