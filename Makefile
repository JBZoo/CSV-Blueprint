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

ifneq (, $(wildcard ./vendor/jbzoo/codestyle/src/init.Makefile))
    include ./vendor/jbzoo/codestyle/src/init.Makefile
endif

DOCKER_IMAGE     ?= jbzoo/csv-blueprint:local
CMD_VALIDATE     := validate:csv --ansi -vvv
BLUEPRINT        := COLUMNS=300 time $(PHP_BIN) ./csv-blueprint $(CMD_VALIDATE)
BLUEPRINT_DOCKER := time docker run --rm  --workdir=/parent-host -v .:/parent-host $(DOCKER_IMAGE) $(CMD_VALIDATE)
BENCH_BIN        := time $(PHP_BIN) ./tests/Benchmarks/bench.php

VALID_CSV       := --csv='./tests/fixtures/demo.csv'
VALID_SCHEMA    := --schema='./tests/schemas/demo_valid.yml'
INVALID_CSV     := --csv='./tests/fixtures/batch/*.csv'
INVALID_SCHEMA  := --schema='./tests/schemas/demo_invalid.yml'

# Build/install ########################################################################################################
build: ##@Project Build project in development mode
	@composer install --optimize-autoloader --ansi
	@rm -f `pwd`/ci-report-converter
	@make build-version

build-prod: ##@Project Build project in production mode
	@composer install --no-dev --classmap-authoritative --no-progress --no-suggest --optimize-autoloader
	@rm -f `pwd`/ci-report-converter
	@make build-version

build-phar-file: ##@Project Build PHAR file
	curl -L "https://github.com/box-project/box/releases/download/4.5.1/box.phar" -o ./build/box.phar
	@make build-version
	@php ./build/box.phar --version
	@php ./build/box.phar compile -vv
	@ls -lh ./build/csv-blueprint.phar

build-version: ##@Project Save version info
	$(eval TAG := $(shell git describe --tags --abbrev=0))
	$(eval BRANCH := $(shell git rev-parse --abbrev-ref HEAD))
	$(eval LAST_COMMIT_DATE := $(shell git log -1 --format=%cI))
	$(eval SHORT_COMMIT_HASH := $(shell git rev-parse --short HEAD))
	$(eval STABLE_FLAG := $(shell git diff --quiet $(TAG) HEAD -- && echo "true" || echo "false"))
	@echo "$(TAG)\n$(STABLE_FLAG)\n$(BRANCH)\n$(LAST_COMMIT_DATE)\n$(SHORT_COMMIT_HASH)" > `pwd`/.version
	@echo "Version info saved to `pwd`/.version"

update: ##@Project Update dependencies
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS) --ansi


# Demo #################################################################################################################
demo: ##@Demo Run demo via PHP binary
	$(call title,"Demo - Valid CSV \(PHP binary\)")
	@$(BLUEPRINT) $(VALID_CSV) $(VALID_SCHEMA)
	$(call title,"Demo - Invalid CSV \(PHP binary\)")
	@$(BLUEPRINT) $(INVALID_CSV) $(INVALID_SCHEMA)

REPORT ?= table
demo-github: ##@Demo Run demo invalid CSV for GitHub Actions
	@$(BLUEPRINT) $(INVALID_CSV) $(INVALID_SCHEMA) --report=$(REPORT)


# Docker ###############################################################################################################
docker-build: ##@Docker (Re-)build Docker image
	$(call title,"Building Docker Image")
	@make build-version
	@docker build -t $(DOCKER_IMAGE) .

docker-demo: ##@Docker Run demo via Docker
	$(call title,"Demo - Valid CSV \(via Docker\)")
	@$(BLUEPRINT_DOCKER) $(VALID_CSV) $(VALID_SCHEMA)
	$(call title,"Demo - Invalid CSV \(via Docker\)")
	@$(BLUEPRINT_DOCKER) $(INVALID_CSV) $(INVALID_SCHEMA)

docker-in: ##@Docker Enter into Docker container
	@docker run -it --entrypoint /bin/sh $(DOCKER_IMAGE)


# Benchmarks ###########################################################################################################
BENCH_ROWS        ?= 5000000
BENCH_CSV         := --csv=./build/bench/5_$(BENCH_ROWS)_header.csv
BENCH_SCHEMA_CELL := --schema=./tests/Benchmarks/benchmark-cell.yml
BENCH_SCHEMA_AGG  := --schema=./tests/Benchmarks/benchmark-agg.yml

bench-create-csv: ##@Benchmarks Create CSV file
	$(call title,"PHP Benchmarks - Create $(BENCH_ROWS) CSV file")
	@mkdir -pv ./build/bench/
	$(BENCH_BIN) --add-header --columns=5 --rows=$(BENCH_ROWS) --ansi
	ls -lah ./build/bench/*.csv;


bench-docker: ##@Benchmarks Run CSV file with Docker
	$(call title,"PHP Benchmarks - CSV file with Docker")
	$(call title,"Only one cell rule")
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMA_CELL) --profile
	$(call title,"Only one aggregation rule")
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMA_AGG)  --profile


bench-php: ##@Benchmarks Run CSV file with PHP binary
	$(call title,"PHP Benchmarks - CSV file with PHP binary")
	$(call title,"Only one cell rule")
	-$(BLUEPRINT) $(BENCH_CSV) $(BENCH_SCHEMA_CELL) --profile
	$(call title,"Only one aggregation rule")
	-$(BLUEPRINT) $(BENCH_CSV) $(BENCH_SCHEMA_AGG)  --profile


BENCH_ROWS_LIST := 100000 1000000
bench-prepare: ##@Benchmarks Create CSV files
	$(call title,"PHP Benchmarks - Prepare CSV files")
	exit 1; # Disabled for now. Enable if you need to generate CSV files.
	@echo "Remove old CSV files"
	mkdir -pv ./build/bench/
	rm -fv    ./build/bench/*.csv
	@$(foreach rows,$(BENCH_ROWS_LIST), \
        echo "Generate CSV: rows=$(rows)"; \
        $(BENCH_BIN) -H --columns=1  --rows=$(rows) -q & \
        $(BENCH_BIN) -H --columns=3  --rows=$(rows) -q & \
        $(BENCH_BIN) -H --columns=5  --rows=$(rows) -q & \
        $(BENCH_BIN) -H --columns=10 --rows=$(rows) -q & \
        $(BENCH_BIN) -H --columns=20 --rows=$(rows) -q & \
        wait; \
        echo "Generate CSV: rows=$(rows) - done"; \
    )
	ls -lh ./build/bench/*.csv;
