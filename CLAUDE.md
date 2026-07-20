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

## Team Collaboration & Use Cases

### Contract-Based Workflow

CSV Blueprint schemas serve as a **data contract** between teams (similar to Swagger/OpenAPI for APIs). Recommended approach: maintain schemas and fixtures in a dedicated repository shared between Data Engineers and Backend Engineers.

**Recommended schema repository structure:**
```
schemas/
  ├── exports/           # Schemas for outgoing data
  │   └── *.yml
  └── imports/           # Schemas for incoming data
      └── *.yml
fixtures/
  └── *.csv              # Sample files for testing
```

### Team-Specific Use Cases

**Data Engineers:**
- Generate schemas from existing CSV: `./csv-blueprint create-schema --csv=export.csv`
- Validate exports in CI/CD before delivery
- Maintain data contracts for downstream consumers

**Backend Engineers:**
- Validate incoming CSV before processing: `./csv-blueprint validate:csv --csv=import.csv --schema=schema.yml`
- Use fixtures from schema repository for import testing
- Catch schema violations early in development

### Change Management

- Schema changes require PR review from both teams
- Breaking changes need coordination and versioning
- Fixtures should be updated alongside schema changes

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
make test
make codestyle
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

- PHP 8.4+
- Extensions: mbstring
- Uses modern PHP features (strict types, readonly properties, etc.)

## GitHub Action Integration

The project can be used as a GitHub Action for CSV validation in CI/CD pipelines:

```yaml
- uses: jbzoo/csv-blueprint@master
  with:
    csv: './path/to/*.csv'
    schema: './path/to/schema.yml'
    report: 'github'  # Available: text, table, github, gitlab, teamcity, junit
    apply-all: 'auto'
    quick: 'no'
```

See [action.yml](action.yml) for all available options and [.github/workflows/demo.yml](.github/workflows/demo.yml) for examples.

## Documentation Guidelines

- **README.md**: Public-facing overview, keep concise
- **CLAUDE.md**: In-depth guidance for contributors and AI assistants
- Avoid duplication; use cross-references between docs
- Prefer practical command examples over verbose explanations
