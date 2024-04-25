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
CMD_VALIDATE     := validate:csv --ansi
BLUEPRINT        := COLUMNS=300 time $(PHP_BIN) ./csv-blueprint $(CMD_VALIDATE)
BLUEPRINT_PHAR   := COLUMNS=300 time $(PHP_BIN) ./build/csv-blueprint.phar $(CMD_VALIDATE)
BLUEPRINT_DOCKER := time docker run --rm  --workdir=/parent-host -v .:/parent-host $(DOCKER_IMAGE) $(CMD_VALIDATE)

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

build-phar-file: ##@Project Build Phar file
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
	@echo "$(TAG) | $(STABLE_FLAG) | $(BRANCH) | $(LAST_COMMIT_DATE) | $(SHORT_COMMIT_HASH)" > `pwd`/.version
	@echo "Version info saved to `pwd`/.version"
	@cat `pwd`/.version

update: ##@Project Update dependencies
	@echo "Composer flags: $(JBZOO_COMPOSER_UPDATE_FLAGS)"
	@composer update $(JBZOO_COMPOSER_UPDATE_FLAGS) --ansi


# Demo #################################################################################################################
demo: ##@Demo Run demo via PHP binary
	$(call title,"Demo - Valid CSV \(PHP binary\)")
	@$(BLUEPRINT) $(VALID_CSV) $(VALID_SCHEMA)
	$(call title,"Demo - Invalid CSV \(PHP binary\)")
	@$(BLUEPRINT) $(INVALID_CSV) $(INVALID_SCHEMA) -vvv

REPORT ?= table
demo-github: ##@Demo Run demo invalid CSV for GitHub Actions
	@$(BLUEPRINT) $(INVALID_CSV) $(INVALID_SCHEMA) -vvv --report=$(REPORT)


# Docker ###############################################################################################################
docker-build: ##@Docker (Re-)build Docker image
	$(call title,"Building Docker Image")
	@docker build -t $(DOCKER_IMAGE) --progress=plain .

docker-demo: ##@Docker Run demo via Docker
	$(call title,"Demo - Valid CSV \(via Docker\)")
	@$(BLUEPRINT_DOCKER) $(VALID_CSV) $(VALID_SCHEMA) -vvv
	$(call title,"Demo - Invalid CSV \(via Docker\)")
	@$(BLUEPRINT_DOCKER) $(INVALID_CSV) $(INVALID_SCHEMA) -vvv

docker-in: ##@Docker Enter into Docker container
	@docker run -it --entrypoint /bin/sh $(DOCKER_IMAGE)


# Benchmarks ###########################################################################################################
BENCH_COLS        ?= 10
BENCH_ROWS_SRC    ?= 2000
BENCH_CSV_PATH    := ./build/bench/$(BENCH_COLS)_$(BENCH_ROWS_SRC)_000.csv
BENCH_CSV         := --csv='$(BENCH_CSV_PATH)'
BENCH_FLAGS       := --debug --profile --report=text -vvv
BENCH_SCHEMAS_ALL := --schema='./tests/Benchmarks/bench_*.yml'
BENCH_SCHEMAS_0   := --schema='./tests/Benchmarks/bench_0_*.yml'
BENCH_SCHEMAS_1   := --schema='./tests/Benchmarks/bench_1_*.yml'
BENCH_SCHEMAS_2   := --schema='./tests/Benchmarks/bench_2_*.yml'
BENCH_SCHEMAS_3   := --schema='./tests/Benchmarks/bench_3_*.yml'


bench: ##@Benchmarks Run all benchmarks
	@make bench-create-csv
	@make bench-docker

bench-create-csv: ##@Benchmarks Create CSV file
	@echo "::group::Creating random CSV file with ${BENCH_COLS} columns and ${BENCH_ROWS_SRC}_000 rows"
	@mkdir -pv ./build/bench/
	@rm -fv    ./build/bench/*.csv
	@time bash ./tests/Benchmarks/create-csv.sh
	@echo "::endgroup::"

bench-docker-quick:
	$(call title,"Single thread mode")
	@docker run --rm  $(DOCKER_IMAGE) --ansi --version
	$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_0) $(BENCH_FLAGS)

bench-docker-quick-parallel:
	$(call title,"Multi-thread mode")
	@docker run --rm  $(DOCKER_IMAGE) --ansi --version
	$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_0) $(BENCH_FLAGS) --parallel

bench-docker: ##@Benchmarks Run CSV file with Docker
	@docker run --rm  $(DOCKER_IMAGE) --ansi --version
	@echo "::group::Quickest"
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_0) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::Minimum"
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_1) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::Realistic"
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_2) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::All aggregations at once"
	-$(BLUEPRINT_DOCKER) $(BENCH_CSV) $(BENCH_SCHEMAS_3) $(BENCH_FLAGS)
	@echo "::endgroup::"


bench-phar: ##@Benchmarks Run CSV file with Phar
	./build/csv-blueprint.phar --ansi --version
	@echo "::group::Quickest"
	-$(BLUEPRINT_PHAR) $(BENCH_CSV) $(BENCH_SCHEMAS_0) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::Minimum"
	-$(BLUEPRINT_PHAR) $(BENCH_CSV) $(BENCH_SCHEMAS_1) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::Realistic"
	-$(BLUEPRINT_PHAR) $(BENCH_CSV) $(BENCH_SCHEMAS_2) $(BENCH_FLAGS)
	@echo "::endgroup::"
	@echo "::group::All aggregations at once"
	-$(BLUEPRINT_PHAR) $(BENCH_CSV) $(BENCH_SCHEMAS_3) $(BENCH_FLAGS)
	@echo "::endgroup::"


bench-php: ##@Benchmarks Run CSV file with classic PHP binary
	$(PHP_BIN) ./csv-blueprint --ansi --version
	-$(BLUEPRINT) $(BENCH_CSV) $(BENCH_SCHEMAS) $(BENCH_FLAGS)

# Extrat tests #########################################################################################################
test-md: ##@Tests Run Markdown tests
	$(call title,"Markdown lint")
	@markdownlint --config .markdownlint.yml *.md
