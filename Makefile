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

REPORT  ?= table
COLUMNS ?= 300

ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif


build:
	@composer install --optimize-autoloader
	@rm -f `pwd`/ci-report-converter


build-prod:
	@composer install --no-dev --classmap-authoritative
	@rm -f `pwd`/ci-report-converter


build-phar-file:
	curl -L "https://github.com/box-project/box/releases/latest/download/box.phar" -o ./box.phar
	@php ./box.phar --version
	@php ./box.phar compile -vv
	@ls -lh ./build/csv-blueprint.phar


update:
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS)


# Demo #################################################################################################################

demo: ##@Project Run all demo commands
	@make demo-valid
	@make demo-invalid


demo-valid: ##@Project Run demo valid CSV
	$(call title,"Demo - Valid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv      \
       --csv=./tests/fixtures/demo.csv            \
       --schema=./tests/schemas/demo_valid.yml    \
       --skip-schema -v

demo-invalid: ##@Project Run demo invalid CSV
	$(call title,"Demo - Invalid CSV")
	@${PHP_BIN} ./csv-blueprint validate:csv        \
       --csv=./tests/fixtures/demo.csv              \
       --schema=./tests/schemas/invalid_schema.yml  \
       --report=$(REPORT) -v


demo-github: ##@Project Run demo invalid CSV
	@${PHP_BIN} ./csv-blueprint validate:csv        \
       --csv=./tests/fixtures/batch/*.csv           \
       --schema=./tests/schemas/demo_invalid.yml    \
       --report=$(REPORT)                           \
       --ansi


# Docker ###############################################################################################################

build-docker:
	$(call title,"Building Docker Image")
	@docker build -t jbzoo/csv-blueprint:local .


docker-in:
	@docker run -it --entrypoint /bin/sh jbzoo/csv-blueprint:local


demo-docker: ##@Project Run demo via Docker
	$(call title,"Demo - Valid CSV \(via Docker\)")
	@docker run --rm                                         \
       -v `pwd`:/parent-host                                 \
       jbzoo/csv-blueprint:local                             \
       validate:csv                                          \
       --csv=/parent-host/tests/fixtures/demo.csv            \
       --schema=/parent-host/tests/schemas/demo_valid.yml    \
       --ansi -vvv
	$(call title,"Demo - Invalid CSV \(via Docker\)")
	@docker run --rm                                         \
       -v `pwd`:/parent-host                                 \
       jbzoo/csv-blueprint:local                             \
       validate:csv                                          \
       --csv=/parent-host/tests/fixtures/demo.csv            \
       --schema=/parent-host/tests/schemas/demo_invalid.yml  \
       --ansi -vvv
