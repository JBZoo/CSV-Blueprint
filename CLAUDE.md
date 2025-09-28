# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CSV Blueprint is a CLI tool for validating CSV files based on customizable YAML schemas. It provides over 330+ validation rules that can be applied to individual cells or entire columns, with support for parallel processing and multiple output formats.

### Core Architecture

The project follows a modular architecture with clear separation of concerns:

- **CLI Layer**: `src/Commands/` - Command classes for different operations (ValidateCsv, CreateSchema, etc.)
- **Schema Engine**: `src/Schema.php` - Core schema definition and parsing
- **Validation Rules**: `src/Rules/` - Two types of validation rules:
  - `Cell/` - Individual cell validation rules (~90 rules)
  - `Aggregate/` - Column-wide aggregate validation rules (~44 rules)
- **CSV Processing**: `src/Csv/` - CSV file handling and column management
- **Workers**: `src/Workers/` - Parallel processing implementation
- **Validators**: `src/Validators/` - Validation orchestration and error reporting

### Key Components

- **Schema Definition**: YAML-based schemas define validation rules for CSV columns
- **Rule System**: Extensible rule system with AbstractRule base class
- **Error Reporting**: Multiple output formats (table, text, GitHub Actions, etc.)
- **Parallel Processing**: Multi-threaded validation for large files
- **CLI Interface**: Built with Symfony Console components

## Development Commands

### Build and Install
```bash
make build              # Install dependencies in development mode
make build-prod         # Install dependencies in production mode
make build-phar-file    # Build standalone PHAR executable
```

### Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/SpecificTest.php

# Run with coverage
./vendor/bin/phpunit --coverage-html build/coverage_html
```

### Code Quality
```bash
# Static analysis with Psalm
./vendor/bin/psalm

# Code style check
./vendor/bin/php-cs-fixer fix --dry-run

# Code style fix
./vendor/bin/php-cs-fixer fix

# Phan static analysis
./vendor/bin/phan
```

### Demo and Validation
```bash
# Run demo validation
make demo

# Validate specific CSV with schema
./csv-blueprint validate:csv --csv=path/to/file.csv --schema=path/to/schema.yml

# Create schema from existing CSV
./csv-blueprint create-schema --csv=path/to/file.csv
```

### Benchmarking
```bash
make bench              # Run full benchmark suite
make bench-docker       # Run benchmarks in Docker
make bench-create-csv   # Generate test CSV files
```

## Schema Structure

Schemas are YAML files that define validation rules:

```yaml
filename_pattern: /pattern\.csv$/
columns:
  - name: "Column Name"
    rules:
      not_empty: true
      length_min: 1
      length_max: 100
    aggregate_rules:
      is_unique: true
      count_min: 1
```

### Rule Categories

- **Cell Rules** (`src/Rules/Cell/`): Validate individual cell values (data types, formats, ranges)
- **Aggregate Rules** (`src/Rules/Aggregate/`): Validate column-wide properties (uniqueness, statistics, counts)

## Testing Strategy

- **Unit Tests**: Located in `tests/` directory
- **Integration Tests**: Test complete validation workflows
- **Benchmark Tests**: Performance testing in `tests/Benchmarks/`
- **Example Schemas**: Test schemas in `tests/schemas/`
- **Fixture Data**: Test CSV files in `tests/fixtures/`

## Docker Support

The project includes Docker support for containerized execution:
- Build: `make docker-build`
- Run: `make docker-demo`
- Interactive: `make docker-in`

## File Organization

- `src/` - Main source code
- `tests/` - Test suite and fixtures
- `schema-examples/` - Example schema files
- `build/` - Build artifacts and tools
- `docker/` - Docker-related files
- `.github/workflows/` - CI/CD pipelines

## PHP Requirements

- PHP 8.3+
- Extensions: mbstring
- Uses modern PHP features (strict types, readonly properties, etc.)