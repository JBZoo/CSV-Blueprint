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
BENCH_BIN        := $(PHP_BIN) ./tests/Benchmarks/bench.php

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
BENCH_COLS        ?= 1
BENCH_ROWS_SRC    ?= 100000
BENCH_CSV_PATH    := ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC)_0.csv
BENCH_CSV         := --csv=$(BENCH_CSV_PATH)
BENCH_SCHEMA_AGG  := --schema=./tests/Benchmarks/benchmark.yml
BENCH_FLAGS       := --debug --profile --report=text


bench-create-csv: ##@Benchmarks Create CSV file
	$(call title,"Benchmark - Create CSV file - $(BENCH_ROWS_SRC)k rows")
	@mkdir -pv ./build/bench/
	@rm -fv    ./build/bench/*.csv
	$(BENCH_BIN) -q --columns=$(BENCH_COLS) --rows=0    --add-header
	$(BENCH_BIN) --columns=$(BENCH_COLS) --rows=$(BENCH_ROWS_SRC)
	cat ./build/bench/$(BENCH_COLS)_header.csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	cat ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC).csv >> $(BENCH_CSV_PATH)
	@wc -l $(BENCH_CSV_PATH)
	@ls -lah ./build/bench/*.csv


bench-docker: ##@Benchmarks Run CSV file with Docker
	$(call title,"Benchmark - CSV file with Docker - $(BENCH_ROWS_SRC)")
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMA_AGG) $(BENCH_FLAGS)


bench-php: ##@Benchmarks Run CSV file with PHP binary
	$(call title,"Benchmark - CSV file with PHP binary - $(BENCH_ROWS_SRC)")
	-$(BLUEPRINT) $(BENCH_CSV) $(BENCH_SCHEMA_AGG) $(BENCH_FLAGS)
