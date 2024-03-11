# JBZoo / Csv-Blueprint

[![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Csv-Blueprint/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Csv-Blueprint?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Csv-Blueprint/coverage.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Csv-Blueprint/level.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/badge)](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/csv-blueprint/version)](https://packagist.org/packages/jbzoo/csv-blueprint/)    [![Total Downloads](https://poser.pugx.org/jbzoo/csv-blueprint/downloads)](https://packagist.org/packages/jbzoo/csv-blueprint/stats)    [![Docker Pulls](https://img.shields.io/docker/pulls/jbzoo/csv-blueprint.svg)](https://hub.docker.com/r/jbzoo/csv-blueprint)    [![Dependents](https://poser.pugx.org/jbzoo/csv-blueprint/dependents)](https://packagist.org/packages/jbzoo/csv-blueprint/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/csv-blueprint)](https://github.com/JBZoo/Csv-Blueprint/blob/master/LICENSE)



* [Introduction](#introduction)
* [Why Validate CSV Files in CI?](#why-validate-csv-files-in-ci)
* [Features](#features)
* [Usage](#usage)
    * [As GitHub Action](#as-github-action)
    * [As Docker container](#as-docker-container)
    * [As PHP binary](#as-php-binary)
    * [As PHP project](#as-php-project)
    * [Schema Definition](#schema-definition)
    * [Schema file examples](#schema-file-examples)
* [Coming soon](#coming-soon)
* [Disadvantages?](#disadvantages)
* [Unit tests and check code style](#unit-tests-and-check-code-style)
* [License](#license)
* [See Also](#see-also)


## Introduction
The JBZoo/Csv-Blueprint tool is a powerful and flexible utility designed for validating CSV files against 
a predefined schema specified in YAML format. With the capability to run both locally and in Docker environments,
JBZoo/Csv-Blueprint is an ideal choice for integrating into CI/CD pipelines, such as GitHub Actions,
to ensure the integrity of CSV data in your projects.

## Why Validate CSV Files in CI?

Validating CSV files at the Continuous Integration (CI) level within a repository is crucial for several reasons in data engineering:

* **Data Quality Assurance**: Ensures that the data meets predefined standards and formats before it's used in applications or analytics, preventing data corruption and inconsistency issues.
* **Early Detection of Errors**: Identifies problems with data submissions or changes early in the development process, reducing the time and effort required for troubleshooting and fixes.
* **Automated Data Governance**: Enforces data governance policies automatically, ensuring that all data complies with regulatory and business rules.
* **Streamlined Data Integration**: Facilitates smoother data integration processes by ensuring that the data being ingested from different sources adheres to the expected schema, minimizing integration errors.
* **Collaboration and Transparency**: Helps teams collaborate more effectively by providing clear standards for data formats and validation rules, leading to more transparent and predictable data handling practices.

Integrating CSV validation into CI processes promotes higher data integrity, reliability, and operational efficiency in data engineering projects.


## Features
* **Schema-based Validation**: Define the structure and rules for your CSV files in an intuitive [YAML format](schema-examples/full.yml), enabling precise validation against your data's expected format.
* **Flexible Configuration**: Support for custom delimiters, quote characters, enclosures, and encoding settings to handle a wide range of CSV formats.
* **Comprehensive Rule Set**: Includes a broad set of validation rules, such as non-empty fields, exact values, regular expressions, numeric constraints, date formats, and more, catering to various data validation needs.
* **Docker Support**: Easily integrate into any workflow with Docker, providing a seamless experience for development, testing, and production environments.
* **GitHub Actions Integration**: Automate CSV validation in your CI/CD pipeline, enhancing the quality control of your data in pull requests and deployments.
* **Various ways to report:** issues that can be easily integrated with GithHub, Gitlab, TeamCity, etc. The default output is a human-readable table. [See Live Demo](https://github.com/JBZoo/Csv-Blueprint).



## Usage

Also see demo in the [GitHub Actions](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml) file.

### As GitHub Action

```yml
      - name: Validate CSV file
        uses: jbzoo/csv-blueprint@master
        with:
          csv: tests/fixtures/demo.csv
          schema: tests/schemas/demo_invalid.yml
          output: table                            # Optional. Default is "github"
```
**Note**. Output format for GitHub Actions is `github` by default. [GitHub Actions friendly](https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-a-warning-message)

This allows you to see bugs in the GitHub interface at the PR level.
That is, the error will be shown in a specific place in the CSV file right in diff of your Pull Requests!

![GitHub Actions - PR](.github/assets/github-actions-pr.png)

<details>
  <summary>Click to see example in GitHub Actions terminal</summary>

  ![GitHub Actions - Terminal](.github/assets/github-actions-termintal.png)

</details>

### As Docker container
Ensure you have Docker installed on your machine.

```sh
# Pull the Docker image
docker pull jbzoo/csv-blueprint

# Run the tool inside Docker
docker run --rm                                  \
    --workdir=/parent-host                       \
    -v `pwd`:/parent-host                        \
    jbzoo/csv-blueprint                          \
    validate:csv                                 \
    --csv=./tests/fixtures/demo.csv              \
    --schema=./tests/schemas/demo_invalid.yml
```


### As PHP binary
Ensure you have PHP installed on your machine.

```sh
wget https://github.com/JBZoo/CI-Report-Converter/releases/latest/download/csv-blueprint.phar
chmod +x ./csv-blueprint.phar
./csv-blueprint.phar validate:csv --csv=./tests/fixtures/demo.csv --schema=./tests/schemas/demo_invalid.yml
```

### As PHP project
Ensure you have PHP installed on your machine.
Then, you can use the following commands to build from source and run the tool.

```sh
git clone git@github.com:jbzoo/csv-blueprint.git csv-blueprint
cd csv-blueprint 
make build
./csv-blueprint validate:csv --csv=./tests/fixtures/demo.csv --schema=./tests/schemas/demo_invalid.yml
```

### Help
```
./csv-blueprint validate:csv --help


Description:
  Validate CSV file by rule

Usage:
  validate:csv [options]

Options:
  -c, --csv=CSV                  CSV filepath to validate. If not set or empty, then the STDIN is used.
  -s, --schema=SCHEMA            Schema rule filepath
  -o, --output=OUTPUT            Report output format. Available options: text, table, github, gitlab, teamcity, junit [default: "table"]
      --no-progress              Disable progress bar animation for logs. It will be used only for text output format.
      --mute-errors              Mute any sort of errors. So exit code will be always "0" (if it's possible).
                                 It has major priority then --non-zero-on-error. It's on your own risk!
      --stdout-only              For any errors messages application will use StdOut instead of StdErr. It's on your own risk!
      --non-zero-on-error        None-zero exit code on any StdErr message.
      --timestamp                Show timestamp at the beginning of each message.It will be used only for text output format.
      --profile                  Display timing and memory usage information.
      --output-mode=OUTPUT-MODE  Output format. Available options:
                                 text - Default text output format, userfriendly and easy to read.
                                 cron - Shortcut for crontab. It's basically focused on human-readable logs output.
                                 It's combination of --timestamp --profile --stdout-only --no-progress -vv.
                                 logstash - Logstash output format, for integration with ELK stack.
                                  [default: "text"]
      --cron                     Alias for --output-mode=cron. Deprecated!
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

### Output Example

As a result of the validation process, you will receive a human-readable table with a list of errors found in the CSV file.
By defualt, the output format is a table, but you can choose from a variety of formats, such as text, GitHub, GitLab, TeamCity, JUnit, and more.
For example, the following output is generated using the "table" format.

**Note**. Output format for GitHub Actions is `github` by default. [GitHub Actions friendly](https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-a-warning-message)
This allows you to see bugs in the GitHub interface at the PR level. That is, the error will be shown in a specific place in the CSV file. See screenshot.

```
CSV    : ./tests/fixtures/demo.csv
Schema : ./tests/schemas/demo_invalid.yml
+------+------------------+--------------+-- demo.csv -------------------------------------------+
| Line | id:Column        | Rule         | Message                                               |
+------+------------------+--------------+-------------------------------------------------------+
| 1    | 1:               | csv.header   | Property "name" is not defined in schema:             |
|      |                  |              | "./tests/schemas/demo_invalid.yml"                    |
| 5    | 2:Float          | max          | Value "74605.944" is greater than "74605"             |
| 5    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red",  |
|      |                  |              | "green", "Blue"]                                      |
| 6    | 0:Name           | min_length   | Value "Carl" (legth: 4) is too short. Min length is 5 |
| 6    | 3:Birthday       | min_date     | Value "1955-05-14" is less than the minimum date      |
|      |                  |              | "1955-05-15T00:00:00.000+00:00"                       |
| 8    | 3:Birthday       | min_date     | Value "1955-05-14" is less than the minimum date      |
|      |                  |              | "1955-05-15T00:00:00.000+00:00"                       |
| 9    | 3:Birthday       | max_date     | Value "2010-07-20" is more than the maximum date      |
|      |                  |              | "2009-01-01T00:00:00.000+00:00"                       |
| 11   | 0:Name           | min_length   | Value "Lois" (legth: 4) is too short. Min length is 5 |
| 11   | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red",  |
|      |                  |              | "green", "Blue"]                                      |
+------+------------------+--------------+-- demo.csv -------------------------------------------+

CSV file is not valid! Found 9 errors.

```


### Schema Definition
Define your CSV validation schema in a YAML file.

This example defines a simple schema for a CSV file with a header row, specifying that the `id` column must not be empty and must contain integer values.
Also, it checks that the `name` column has a minimum length of 3 characters.

```yml
csv:
  delimiter: ,
  quote_char: \
  enclosure: "\""

columns:
  - name: id
    rules:
      not_empty: true
      is_int: true

  - name: name
    rules:
      min_length: 3

```

### Schema file examples

In the [example Yml file](schema-examples/full.yml) you can find a detailed description of all features.

**Important notes**
* I have deliberately refused typing of columns (like `type: integer`) and replaced them with rules,
which can be combined in any sequence and completely at your discretion.
This gives you great flexibility when validating CSV files.

* All fields (unless explicitly stated otherwise) are optional and you can choose not to declare them. Up to you.

```yml
# It's a full example of the CSV schema file in YAML format.

csv: # Here are default values. You can skip this section if you don't need to override the default values
  header: true                          # If the first row is a header. If true, name of each column is required
  delimiter: ,                          # Delimiter character in CSV file
  quote_char: \                         # Quote character in CSV file
  enclosure: "\""                       # Enclosure for each field in CSV file
  encoding: utf-8                       # Only utf-8, utf-16, utf-32 (Experimental)
  bom: false                            # If the file has a BOM (Byte Order Mark) at the beginning (Experimental)

columns:
  - name: "csv_header_name"             # Any custom name of the column in the CSV file (first row). Required if "csv_structure.header" is true.
    description: "Lorem ipsum"          # Optional. Description of the column. Not used in the validation process.
    rules:
      # You can use the rules in any combination. Or not use any of them.
      # They are grouped below simply for ease of navigation and reading.
      # If you see the value for the rule is "true" - that's just an enable flag.
      # In other cases, these are rule parameters.

      # General rules
      not_empty: true                   # Value is not empty string. Ignore spaces.
      exact_value: Some string          # Case-sensitive. Exact value for string in the column
      allow_values: [ y, n, "" ]        # Strict set of values that are allowed. Case-sensitive.

      # Strings only
      regex: /^[\d]{2}$/                # Any valid regex pattern. See https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
      min_length: 1                     # Integer only. Min length of the string with spaces
      max_length: 10                    # Integer only. Max length of the string with spaces
      only_trimed: true                 # Only trimed strings. Example: "Hello World" (not " Hello World ")
      only_lowercase: true              # String is only lower-case. Example: "hello world"
      only_uppercase: true              # String is only upper-case. Example: "HELLO WORLD"
      only_capitalize: true             # String is only capitalized. Example: "Hello World"

      # Decimal and integer numbers
      min: 10                           # Can be integer or float, negative and positive
      max: 100.50                       # Can be integer or float, negative and positive
      precision: 2                      # Strict(!) number of digits after the decimal point

      # Dates
      date_format: Y-m-d                # See: https://www.php.net/manual/en/datetime.format.php
      min_date: "2000-01-02"            # See examples https://www.php.net/manual/en/function.strtotime.php
      max_date: "+1 day"                # See examples https://www.php.net/manual/en/function.strtotime.php

      # Specific formats
      is_bool: true                     # Allow only boolean values "true" and "false", case-insensitive
      is_int: true                      # Check format only. Can be negative and positive. Without any separators
      is_float: true                    # Check format only. Can be negative and positive. Dot as decimal separator
      is_ip: true                       # Only IPv4. Example: "127.0.0.1"
      is_url: true                      # Only URL format. Example: "https://example.com/page?query=string#anchor"
      is_email: true                    # Only email format. Example: "user@example.com"
      is_domain: true                   # Only domain name. Example: "example.com"
      is_uuid4: true                    # Only UUID4 format. Example: "550e8400-e29b-41d4-a716-446655440000"
      is_latitude: true                 # Can be integer or float. Example: 50.123456
      is_longitude: true                # Can be integer or float. Example: -89.123456
      cardinal_direction: true          # Valid cardinal direction. Examples: "N", "S", "NE", "SE", "none", ""
      usa_market_name: true             # Check if the value is a valid USA market name. Example: "New York, NY"

```


<details>
  <summary>Click to see: JSON Format</summary>

```json
{
    "csv"     : {
        "header"     : true,
        "delimiter"  : ",",
        "quote_char" : "\\",
        "enclosure"  : "\"",
        "encoding"   : "utf-8",
        "bom"        : false
    },
    "columns" : [
        {
            "name"        : "csv_header_name",
            "description" : "Lorem ipsum",
            "rules"       : {
                "not_empty"          : true,
                "exact_value"        : "Some string",
                "allow_values"       : ["y", "n", ""],
                "regex"              : "\/^[\\d]{2}$\/",
                "min_length"         : 1,
                "max_length"         : 10,
                "only_trimed"        : true,
                "only_lowercase"     : true,
                "only_uppercase"     : true,
                "only_capitalize"    : true,
                "min"                : 10,
                "max"                : 100.5,
                "precision"          : 2,
                "date_format"        : "Y-m-d",
                "min_date"           : "2000-01-02",
                "max_date"           : "+1 day",
                "is_bool"            : true,
                "is_int"             : true,
                "is_float"           : true,
                "is_ip"              : true,
                "is_url"             : true,
                "is_email"           : true,
                "is_domain"          : true,
                "is_uuid4"           : true,
                "is_latitude"        : true,
                "is_longitude"       : true,
                "cardinal_direction" : true,
                "usa_market_name"    : true
            }
        }
    ]
}

```

</details>



<details>
  <summary>Click to see: PHP Format</summary>

```php
<?php
declare(strict_types=1);

return [
    'csv' => [
        'header'     => true,
        'delimiter'  => ',',
        'quote_char' => '\\',
        'enclosure'  => '"',
        'encoding'   => 'utf-8',
        'bom'        => false,
    ],
    'columns' => [
        [
            'name'        => 'csv_header_name',
            'description' => 'Lorem ipsum',
            'rules'       => [
                'not_empty'          => true,
                'exact_value'        => 'Some string',
                'allow_values'       => ['y', 'n', ''],
                'regex'              => '/^[\\d]{2}$/',
                'min_length'         => 1,
                'max_length'         => 10,
                'only_trimed'        => true,
                'only_lowercase'     => true,
                'only_uppercase'     => true,
                'only_capitalize'    => true,
                'min'                => 10,
                'max'                => 100.5,
                'precision'          => 2,
                'date_format'        => 'Y-m-d',
                'min_date'           => '2000-01-02',
                'max_date'           => '+1 day',
                'is_bool'            => true,
                'is_int'             => true,
                'is_float'           => true,
                'is_ip'              => true,
                'is_url'             => true,
                'is_email'           => true,
                'is_domain'          => true,
                'is_uuid4'           => true,
                'is_latitude'        => true,
                'is_longitude'       => true,
                'cardinal_direction' => true,
                'usa_market_name'    => true,
            ],
        ],
    ],
];

```

</details>


## Coming soon

* [ ] Filename pattern validation with regex (like "all files in the folder should be in the format `/^[\d]{4}-[\d]{2}-[\d]{2}\.csv$/`").
* [ ] CSV/Schema file discovery in the folder with regex filename pattern (like `glob(./**/dir/*.csv)`).
* [ ] Agregate rules (like "at least one of the fields should be not empty" or "all fields should be unique").
* [ ] Create CSV files based on the schema (like "create 1000 rows with random data based on rules").
* [ ] Checking multiple CSV files in one schema.
* [ ] Using multiple schemas for one csv file.
* [ ] Parallel validation of really-really large files (1GB+ ?). I know you have them and not so much memory.
* [ ] Parallel validation of multiple files at once.
* [ ] Benchmarks as part of the CI process and Readme. It's important to know how much time the validation process takes.
* [ ] Inheritance of schemas, rules and columns. Define parent schema and override some rules in the child schemas. Make it DRY and easy to maintain.
* [ ] More output formats (like JSON, XML, etc). Any ideas?
* [ ] Complex rules (like "if field `A` is not empty, then field `B` should be not empty too").
* [ ] Input encoding detection + `BOM` (right now it's experimental). It works but not so accurate... UTF-8/16/32 is the best choice for now.
* [ ] Extending with custom rules and custom output formats. Plugins?
* [ ] More examples and documentation.


## Disadvantages?

* Yeah-yeah-yeah. I know it's not the fastest tool in the world. But it's not the slowest either.
* Yeah-yeah-yeah. It's PHP (not a Python, Go). PHP is not the best language for such tasks.
* Yeah-yeah-yeah. It looks like a standalone binary.
* Yeah-yeah-yeah. You can't use as Python SDK.

But... it's not a problem for most cases. And it solves the problem of validating CSV files in CI.

The utility is made to just pick up and use and not think about how it works internally.
Moreover, everything is covered as strictly as possible by tests and strict typing of variables
(as strictly as possible in today's PHP world).

**Interesting fact, by the way**

The first version was written from scratch in about 3 days (with baby breaks). I'm looking at the first commit and the very first git tag. I'd say over the weekend, in my spare time on my personal laptop. AI I only used for this Readme file. I'm not very good at English. 😅


## Contributing
If you have any ideas or suggestions, feel free to open an issue or create a pull request.

```sh
# Fork the repo and build project
git clone git@github.com:jbzoo/csv-blueprint.git ./jbzoo-csv-blueprint
cd ./jbzoo-csv-blueprint
make build

# Make your local changes

# Run all tests and check code style
make test
make codestyle

# Create your pull request and check all tests in CI (Github Actions)
```


### License

MIT


## See Also

- [CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) - It converts different error reporting standards for deep compatibility with popular CI systems.
- [Composer-Diff](https://github.com/JBZoo/Composer-Diff) - See what packages have changed after `composer update`.
- [Composer-Graph](https://github.com/JBZoo/Composer-Graph) - Dependency graph visualization of composer.json based on mermaid-js.
- [Mermaid-PHP](https://github.com/JBZoo/Mermaid-PHP) - Generate diagrams and flowcharts with the help of the mermaid script language.
- [Utils](https://github.com/JBZoo/Utils) - Collection of useful PHP functions, mini-classes, and snippets for every day.
- [Image](https://github.com/JBZoo/Image) - Package provides object-oriented way to manipulate with images as simple as possible.
- [Data](https://github.com/JBZoo/Data) - Extended implementation of ArrayObject. Use files as config/array.
- [Retry](https://github.com/JBZoo/Retry) - Tiny PHP library providing retry/backoff functionality with multiple backoff strategies and jitter support.
