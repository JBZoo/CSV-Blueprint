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
	curl -L "https://github.com/box-project/box/releases/download/4.5.1/box.phar" -o ./build/box.phar
	@php ./build/box.phar --version
	@php ./build/box.phar compile -vv
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
	@docker run --rm                                \
       --workdir=/parent-host                       \
       -v .:/parent-host                            \
       jbzoo/csv-blueprint:local                    \
       validate:csv                                 \
       --csv=./tests/fixtures/demo.csv              \
       --schema=./tests/schemas/demo_valid.yml      \
       --ansi -vvv
	$(call title,"Demo - Invalid CSV \(via Docker\)")
	@docker run --rm                                \
       --workdir=/parent-host                       \
       -v .:/parent-host                            \
       jbzoo/csv-blueprint:local                    \
       validate:csv                                 \
       --csv=./tests/fixtures/demo.csv              \
       --schema=./tests/schemas/demo_invalid.yml    \
       --ansi -vvv

# Benchmarks ###########################################################################################################

BENCH_ROWS ?= 1000000
BENCH_CSV ?= --csv=./build/1000000.csv
BENCH_SCHEMA ?= --schema=./tests/benchmarks/benchmark.yml

bench-prepare: ##@Project Run PHP benchmarks
	$(call title,"PHP Benchmarks - Prepare CSV files")
	${PHP_BIN} ./tests/benchmarks/create-csv.php $(BENCH_ROWS)
	ls -lh ./build/*.csv

bench-php: ##@Project Run PHP benchmarks
	$(call title,"PHP Benchmarks - PHP binary")
	${PHP_BIN} ./csv-blueprint validate:csv $(BENCH_CSV) $(BENCH_SCHEMA) --ansi -vvv

bench-docker: ##@Project Run Docker benchmarks
	$(call title,"PHP Benchmarks - Docker")
	@docker run --rm                                  \
         --workdir=/parent-host                       \
         -v .:/parent-host                            \
         jbzoo/csv-blueprint:local                    \
         validate:csv                                 \
         $(BENCH_CSV) $(BENCH_SCHEMA) --ansi -vvv
