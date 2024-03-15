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

.PHONY: build

REPORT       ?= table
COLUMNS_TEST ?= 150

ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif


build: ##@Project Install all 3rd party dependencies
	$(call title,"Install/Update all 3rd party dependencies")
	@composer install
	@make build-phar
	@rm -f `pwd`/ci-report-converter


build-install: ##@Project Install all 3rd party dependencies as prod
	$(call title,"Install/Update all 3rd party dependencies as prod")
	@composer install --no-dev --no-progress --no-interaction --no-suggest --optimize-autoloader
	@rm -f `pwd`/ci-report-converter


update: ##@Project Install/Update all 3rd party dependencies
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS)
	@make build-phar


test-all: ##@Project Run all project tests at once
	@make test
	@make codestyle


build-docker:
	$(call title,"Building Docker Image")
	@docker build -t jbzoo/csv-blueprint .


demo-valid: ##@Project Run demo valid CSV
	$(call title,"Demo - Valid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_valid.yml

demo-docker: ##@Project Run demo via Docker
	$(call title,"Demo - Valid CSV \(via Docker\)")
	@docker run --rm                                         \
       -v `pwd`:/parent-host                                 \
       jbzoo/csv-blueprint                                   \
       validate:csv                                          \
       --csv=/parent-host/tests/fixtures/demo.csv            \
       --schema=/parent-host/tests/schemas/demo_valid.yml    \
       --ansi
	$(call title,"Demo - Invalid CSV \(via Docker\)")
	@docker run --rm                                         \
       -v `pwd`:/parent-host                                 \
       jbzoo/csv-blueprint                                   \
       validate:csv                                          \
       --csv=/parent-host/tests/fixtures/demo.csv            \
       --schema=/parent-host/tests/schemas/demo_invalid.yml  \
       --ansi


demo-invalid: ##@Project Run demo invalid CSV
	$(call title,"Demo - Invalid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_invalid.yml  \
       --report=$(REPORT)


demo-github: ##@Project Run demo invalid CSV
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/batch/*.csv         \
       --schema=./tests/schemas/demo_invalid.yml  \
       --report=$(REPORT)                         \
       --ansi


demo: ##@Project Run all demo commands
	@make demo-valid
	@make demo-invalid
