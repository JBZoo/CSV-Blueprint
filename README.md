# JBZoo / Csv-Blueprint

[![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Csv-Blueprint/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Csv-Blueprint?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Csv-Blueprint/coverage.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Csv-Blueprint/level.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/badge)](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/csv-blueprint/version)](https://packagist.org/packages/jbzoo/csv-blueprint/)    [![Total Downloads](https://poser.pugx.org/jbzoo/csv-blueprint/downloads)](https://packagist.org/packages/jbzoo/csv-blueprint/stats)    [![Docker Pulls](https://img.shields.io/docker/pulls/jbzoo/csv-blueprint.svg)](https://hub.docker.com/r/jbzoo/csv-blueprint)    [![Dependents](https://poser.pugx.org/jbzoo/csv-blueprint/dependents)](https://packagist.org/packages/jbzoo/csv-blueprint/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/csv-blueprint)](https://github.com/JBZoo/Csv-Blueprint/blob/master/LICENSE)

<!-- rules-counter -->
[![Static Badge](https://img.shields.io/badge/Rules-103-green?label=Total%20Number%20of%20Rules&labelColor=darkgreen&color=gray)](schema-examples/full.yml)    [![Static Badge](https://img.shields.io/badge/Rules-55-green?label=Cell%20Value&labelColor=blue&color=gray)](src/Rules/Cell)    [![Static Badge](https://img.shields.io/badge/Rules-45-green?label=Aggregate%20Column&labelColor=blue&color=gray)](src/Rules/Aggregate)    [![Static Badge](https://img.shields.io/badge/Rules-3-green?label=Extra%20Checks&labelColor=blue&color=gray)](schema-examples/full.yml)    [![Static Badge](https://img.shields.io/badge/Rules-328-green?label=Plan%20to%20add&labelColor=gray&color=gray)](tests/schemas/todo.yml)
<!-- /rules-counter -->

## Introduction

The CSV Blueprint tool is a powerful and flexible utility designed for validating CSV files against 
a pre-defined schema specified in YAML format. With the capability to run both locally and in Docker environments,
CSV Blueprint is an ideal choice for integrating into CI/CD pipelines, such as GitHub Actions,
to ensure the integrity of CSV data in your projects.


### Why validate CSV files

Validating CSV files at the Continuous Integration (CI) level within a repository is crucial for several reasons in data engineering:

* **Data Quality Assurance**: Ensures that the data meets predefined standards and formats before it's used in applications or analytics, preventing data corruption and inconsistency issues.
* **Early Detection of Errors**: Identifies problems with data submissions or changes early in the development process, reducing the time and effort required for troubleshooting and fixes.
* **Automated Data Governance**: Enforces data governance policies automatically, ensuring that all data complies with regulatory and business rules.
* **Streamlined Data Integration**: Facilitates smoother data integration processes by ensuring that the data being ingested from different sources adheres to the expected schema, minimizing integration errors.
* **Collaboration and Transparency**: Helps teams collaborate more effectively by providing clear standards for data formats and validation rules, leading to more transparent and predictable data handling practices.

Integrating CSV validation into CI processes promotes higher data integrity, reliability, and operational efficiency in data engineering projects.


### Features

* **Schema-based Validation**: Define the structure and rules for your CSV files in an intuitive [YAML format](schema-examples/full.yml), enabling precise validation against your data's expected format.
* **Flexible Configuration**: Support for custom delimiters, quote characters, enclosures, and encoding settings to handle a wide range of CSV formats.
* **Comprehensive Rule Set**: Includes a broad set of validation rules, such as non-empty fields, exact values, regular expressions, numeric constraints, date formats, and more, catering to various data validation needs.
* **Docker Support**: Easily integrate into any workflow with Docker, providing a seamless experience for development, testing, and production environments.
* **GitHub Actions Integration**: Automate CSV validation in your CI/CD pipeline, enhancing the quality control of your data in pull requests and deployments.
* **Various ways to report:** issues that can be easily integrated with GitHub, Gitlab, TeamCity, etc. The default output is a human-readable table. [See Live Demo](https://github.com/JBZoo/Csv-Blueprint-Demo).


### Live Demo

* As a live demonstration of how the tool works you can take a look at the super minimal repository - [JBZoo/Csv-Blueprint-Demo](https://github.com/JBZoo/Csv-Blueprint-Demo). Feel free to fork it and play with the tool.
* You can see more complex examples and different ways of reporting by looking at the [last Demo pipeline](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml?query=branch%3Amaster) (please open the logs). There you'll find the basic ways to get started. And also the `All Report Types` (left sidebar) link with the different report types.

**See also**
* [PR as a live demo](https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files)
* [.github/workflows/demo.yml](.github/workflows/demo.yml)
* [demo_invalid.yml](tests/schemas/demo_invalid.yml)
* [demo_valid.yml](tests/schemas/demo_valid.yml)
* [demo.csv](tests/fixtures/demo.csv)


## Schema definition
Define your CSV validation schema in a [YAML](schema-examples/full.yml). Other formats are also available: [JSON](schema-examples/full.json), [PHP](schema-examples/full.php).

This example defines a simple schema for a CSV file with a header row, specifying that the `id` column must not be empty and must contain integer values.
Also, it checks that the `name` column has a minimum length of 3 characters.

```yml
csv:
  delimiter: ;

columns:
  - name: id
    rules:
      not_empty: true
      is_int: true

  - name: name
    rules:
      min_length: 3

```


### Full description of the schema

In the [example Yml file](schema-examples/full.yml) you can find a detailed description of all features.
It's also covered by tests, so it's always up-to-date.

**Important notes**
* I have deliberately refused typing of columns (like `type: integer`) and replaced them with rules,
  which can be combined in any sequence and completely at your discretion.
  This gives you great flexibility when validating CSV files.
* All fields (unless explicitly stated otherwise) are optional, and you can choose not to declare them. Up to you.
* You are always free to add your option anywhere (except the `rules` list) and it will be ignored. I find it convenient for additional integrations and customization.


<!-- full-yml -->
```yml
# It's a complete example of the CSV schema file in YAML format.
# See copy of the file without comments here ./schema-examples/full_clean.yml

# Just meta
name: CSV Blueprint Schema Example      # Name of a CSV file. Not used in the validation process.
description: |                          # Any description of the CSV file. Not used in the validation process.
  This YAML file provides a detailed description and validation rules for CSV files
  to be processed by JBZoo/Csv-Blueprint tool. It includes specifications for file name patterns,
  CSV formatting options, and extensive validation criteria for individual columns and their values,
  supporting a wide range of data validation rules from basic type checks to complex regex validations.
  This example serves as a comprehensive guide for creating robust CSV file validations.


# Regular expression to match the file name. If not set, then no pattern check.
# This allows you to pre-validate the file name before processing its contents.
# Feel free to check parent directories as well.
# See https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
filename_pattern: /demo(-\d+)?\.csv$/i


# Here are default values to parse CSV file.
# You can skip this section if you don't need to override the default values.
csv:
  header: true                          # If the first row is a header. If true, name of each column is required.
  delimiter: ,                          # Delimiter character in CSV file.
  quote_char: \                         # Quote character in CSV file.
  enclosure: "\""                       # Enclosure for each field in CSV file.
  encoding: utf-8                       # (Experimental) Only utf-8, utf-16, utf-32.
  bom: false                            # (Experimental) If the file has a BOM (Byte Order Mark) at the beginning.


# Description of each column in CSV.
# It is recommended to present each column in the same order as presented in the CSV file.
# This will not affect the validator, but will make it easier for you to navigate.
# For convenience, use the first line as a header (if possible).
columns:
  - name: "Column Name (header)"        # Any custom name of the column in the CSV file (first row). Required if "csv_structure.header" is true.
    description: "Lorem ipsum"          # Description of the column. Not used in the validation process.
    example: "Some example"             # Example of the column value. Not used in the validation process.

    # Important notes about the validation rules.
    # 1. All rules except "not_empty" ignored for empty strings (length 0).
    #    If the value must be non-empty, use "not_empty" as extra rule!
    # 2. All rules don't depend on each other. They are independent.
    #    They know nothing about each other and cannot influence each other.
    # 3. You can use the rules in any combination. Or not use any of them.
    #    They are grouped below simply for ease of navigation and reading.
    # 4. If you see the value for the rule is "true" - that's just an enable flag.
    #    In other cases, these are rule parameters.
    # 5. The order of rules execution is the same as in the schema. But it doesn't matter.
    #    The result will be the same in any order.
    # 6. Most of the rules are case-sensitive. Unless otherwise specified.
    # 7. As backup plan, you always can use the "regex" rule.

    ####################################################################################################################
    # Data validation for each(!) value in the column.
    # Of course, this can greatly affect the speed of checking.
    # It depends on the number of checks and CSV file size.
    # TODO: There are several ways to optimize this process, but the author needs time to test it carefully.
    rules:
      # General rules
      not_empty: true                   # Value is not an empty string. Actually checks if the string length is not 0.
      exact_value: Some string          # Exact value for string in the column.
      allow_values: [ y, n, "" ]        # Strict set of values that are allowed.
      not_allow_values: [ invalid ]     # Strict set of values that are NOT allowed.

      # Any valid regex pattern. See https://www.php.net/manual/en/reference.pcre.pattern.syntax.php.
      # Of course it's an ultimatum to verify any sort of string data.
      # Please, be careful. Regex is a powerful tool, but it can be very dangerous if used incorrectly.
      # Remember that if you want to solve a problem with regex, you now have two problems.
      # But have it your way, then happy debugging! https://regex101.com.
      regex: /^[\d]{2}$/

      # Checks length of a string including spaces (multibyte safe).
      length: 5
      length_not: 4
      length_min: 1
      length_max: 10

      # Basic string checks
      is_trimmed: true                  # Only trimmed strings. Example: "Hello World" (not " Hello World ").
      is_lowercase: true                # String is only lower-case. Example: "hello world".
      is_uppercase: true                # String is only upper-case. Example: "HELLO WORLD".
      is_capitalize: true               # String is only capitalized. Example: "Hello World".

      # Count number of words used in a string.
      # Note that multibyte locales are not supported.
      # Example: "Hello World, 123" - 2 words only (123 is not a word).
      word_count: 5
      word_count_not: 4
      word_count_min: 1
      word_count_max: 10

      # Contains rules
      contains: Hello                   # Example: "Hello World".
      contains_one: [ a, b ]            # At least one of the string must be in the CSV value.
      contains_all: [ a, b, c ]         # All the strings must be part of a CSV value.
      starts_with: "prefix "            # Example: "prefix Hello World".
      ends_with: " suffix"              # Example: "Hello World suffix".

      # Under the hood it converts and compares as float values.
      # Comparison accuracy is 10 digits after a dot.
      # Scientific number format is also supported. Example: "1.2e3".
      num: 5                            # You can use integers.
      num_not: 4.123                    # Float numbers.
      num_min: 1.2e3                    # And even scientific format.
      num_max: -10.123                  # Negative and positive, zero is also supported.
      is_int: true                      # Check format only. Can be negative and positive. Without any separators.
      is_float: true                    # Check format only. Can be negative and positive. Dot as decimal separator.

      # Number of digits after the decimal point (with zeros).
      precision: 5
      precision_not: 4
      precision_min: 1
      precision_max: 10

      # Dates. Under the hood, the strings are converted to timestamp and compared.
      # This gives you the ability to use relative dates and any formatting you want.
      # By default, it works in UTC. But you can specify your own timezone as part of the date string.
      # Format:    https://www.php.net/manual/en/datetime.format.php.
      # Parsing:   https://www.php.net/manual/en/function.strtotime.php.
      # Timezones: https://www.php.net/manual/en/timezones.php.
      date: 01 Jan 2000                 # You can use any string that can be parsed by the strtotime function.
      date_not: 2006-01-02 15:04:05 -0700 Europe/Rome
      date_min: +1 day                  # Examples of relative formats.
      date_max: now                     # Examples of current date and time.
      date_format: Y-m-d                # Check strict format of the date.
      is_date: true                     # Accepts arbitrary date format. Is shows error if failed to convert to timestamp.

      # Specific formats
      is_bool: true                     # Allow only boolean values "true" and "false", case-insensitive.
      is_ip4: true                      # Only IPv4. Example: "127.0.0.1".
      is_url: true                      # Only URL format. Example: "https://example.com/page?query=string#anchor".
      is_email: true                    # Only email format. Example: "user@example.com".
      is_domain: true                   # Only domain name. Example: "example.com".
      is_uuid: true                     # Validates whether the input is a valid UUID. It also supports validation of specific versions 1, 3, 4 and 5.
      is_alias: true                    # Only alias format. Example: "my-alias-123". It can contain letters, numbers, and dashes.
      is_currency_code: true            # Validates an ISO 4217 currency code like GBP or EUR. Case-sensitive. See: https://en.wikipedia.org/wiki/ISO_4217.
      is_base64: true                   # Validate if a string is Base64-encoded. Example: "cmVzcGVjdCE=".

      # Validates if the given input is a valid JSON.
      # This is possible if you escape all special characters correctly and use a special CSV format.
      is_json: true                     # Example: {"foo":"bar"}.

      # Geography
      is_latitude: true                 # Can be integer or float. Example: 50.123456.
      is_longitude: true                # Can be integer or float. Example: -89.123456.
      is_geohash: true                  # Check if the value is a valid geohash. Example: "u4pruydqqvj".
      is_cardinal_direction: true       # Valid cardinal direction. Examples: "N", "S", "NE", "SE", "none", "".
      is_usa_market_name: true          # Check if the value is a valid USA market name. Example: "New York, NY".

      # Validates whether the input is a country code in ISO 3166-1 standard.
      # Available options: "alpha-2" (Ex: "US"), "alpha-3" (Ex: "USA"), "numeric" (Ex: "840").
      # The rule uses data from iso-codes: https://salsa.debian.org/iso-codes-team/iso-codes.
      country_code: alpha-2             # Country code in ISO 3166-1 standard. Examples: "US", "USA", "840".

      # Validates whether the input is language code based on ISO 639.
      # Available options: "alpha-2" (Ex: "en"), "alpha-3" (Ex: "eng").
      # See: https://en.wikipedia.org/wiki/ISO_639.
      language_code: alpha-2            # Examples: "en", "eng".


    ####################################################################################################################
    # Data validation for the entire(!) column using different data aggregation methods.
    # Depending on the file size and the chosen aggregation method - this can use a lot of RAM time.
    # Be careful with files that are 2-3 or more times larger than the available memory.
    # TODO: There are several ways to optimize this process, but the author needs time to test it carefully.
    aggregate_rules:
      is_unique: true                   # All values in the column are unique.

      # Sum of the numbers in the column. Example: [1, 2, 3] => 6.
      sum: 5.123
      sum_not: 4.123
      sum_min: 1.123
      sum_max: 10.123

      # Regular the arithmetic mean. The sum of the numbers divided by the count.
      average: 5.123
      average_not: 4.123
      average_min: 1.123
      average_max: 10.123

      # Total number of rows in the CSV file.
      # Since any(!) values are taken into account, it only makes sense to use these rules once in any column.
      count: 5
      count_not: 4
      count_min: 1
      count_max: 10

      # Counts only empty values (string length is 0).
      count_empty: 5
      count_empty_not: 4
      count_empty_min: 1
      count_empty_max: 10

      # Counts only not empty values (string length is not 0).
      count_not_empty: 5
      count_not_empty_not: 4
      count_not_empty_min: 1
      count_not_empty_max: 10

      # Calculate the median average of a list of numbers.
      median: 5.123
      median_not: 4.123
      median_min: 1.123
      median_max: 10.123

      # Population variance - Use when all possible observations of the system are present.
      # If used with a subset of data (sample variance), it will be a biased variance.
      # n degrees of freedom, where n is the number of observations.
      population_variance: 5.123
      population_variance_not: 4.123
      population_variance_min: 1.123
      population_variance_max: 10.123

      # Unbiased sample variance Use when only a subset of all possible observations of the system are present.
      # n - 1 degrees of freedom, where n is the number of observations.
      sample_variance: 5.123
      sample_variance_not: 4.123
      sample_variance_min: 1.123
      sample_variance_max: 10.123

      # Standard deviation (For a sample; uses sample variance).
      # Standard deviation is a measure that is used to quantify the amount of variation or dispersion of a set of data values.
      # A low standard deviation indicates that the data points tend to be close to the mean (also called the expected value) of the set.
      # A high standard deviation indicates that the data points are spread out over a wider range of values.
      # See: https://en.wikipedia.org/wiki/Standard_deviation.
      sd_sample: 5.123
      sd_sample_not: 4.123
      sd_sample_min: 1.123
      sd_sample_max: 10.123

      # SD+ (Standard deviation for a population; uses population variance).
      sd_population: 5.123
      sd_population_not: 4.123
      sd_population_min: 1.123
      sd_population_max: 10.123

      # Coefficient of variation (cᵥ) Also known as relative standard deviation (RSD).
      # A standardized measure of dispersion of a probability distribution or frequency distribution.
      # It is often expressed as a percentage. The ratio of the standard deviation to the mean.
      # See: https://en.wikipedia.org/wiki/Coefficient_of_variation.
      coef_of_var: 5.123
      coef_of_var_not: 4.123
      coef_of_var_min: 1.123
      coef_of_var_max: 10.123

  - name: "another_column"
    rules:
      not_empty: true

  - name: "third_column"
    rules:
      not_empty: true

```
<!-- /full-yml -->

## Usage

You can find launch examples in the [workflow demo](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml).


### GitHub Action

<!-- github-actions-yml -->
```yml
- uses: jbzoo/csv-blueprint@master # See the specific version on releases page
  with:
    # Path(s) to validate. You can specify path in which CSV files will be searched. Feel free to use glob pattrens. Usage examples: /full/path/file.csv, p/file.csv, p/*.csv, p/**/*.csv, p/**/name-*.csv, **/*.csv, etc.
    # Required: true
    csv: ./tests/**/*.csv

    # Schema filepath. It can be a YAML, JSON or PHP. See examples on GitHub.
    # Required: true
    schema: ./tests/schema.yml

    # Report format. Available options: text, table, github, gitlab, teamcity, junit
    # Default value: github
    # You can skip it
    report: github

    # Quick mode. It will not validate all rows. It will stop after the first error.
    # Default value: no
    # You can skip it
    quick: no

    # Skip schema validation. If you are sure that the schema is correct, you can skip this check.
    # Default value: no
    # You can skip it
    skip-schema: no

```
<!-- /github-actions-yml -->

**Note**. GitHub Actions report format is `github` by default. See [GitHub Actions friendly](https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-a-warning-message) and [PR as a live demo](https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files). 

This allows you to see bugs in the GitHub interface at the PR level.
That is, the error will be shown in a specific place in the CSV file right in diff of your Pull Requests! [See example](https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files).

![GitHub Actions - PR](.github/assets/github-actions-pr.png)

<details>
  <summary>Click to see example in GitHub Actions terminal</summary>

  ![GitHub Actions - Terminal](.github/assets/github-actions-termintal.png)

</details>


### Docker container
Ensure you have Docker installed on your machine.

```sh
# Pull the Docker image
docker pull jbzoo/csv-blueprint

# Run the tool inside Docker
docker run --rm                                  \
    --workdir=/parent-host                       \
    -v $(pwd):/parent-host                       \
    jbzoo/csv-blueprint                          \
    validate:csv                                 \
    --csv=./tests/fixtures/demo.csv              \
    --schema=./tests/schemas/demo_invalid.yml
```


### PHP binary
Ensure you have PHP installed on your machine.

**Status: WIP**. It's not released yet. But you can build it from source. See manual above and `./build/csv-blueprint.phar` file.

```sh
wget https://github.com/JBZoo/Csv-Blueprint/releases/latest/download/csv-blueprint.phar
chmod +x ./csv-blueprint.phar
./csv-blueprint.phar validate:csv               \
   --csv=./tests/fixtures/demo.csv              \
   --schema=./tests/schemas/demo_invalid.yml
```


### PHP project
Ensure you have PHP installed on your machine.
Then, you can use the following commands to build from source and run the tool.

```sh
git clone git@github.com:jbzoo/csv-blueprint.git csv-blueprint
cd csv-blueprint 
make build
./csv-blueprint validate:csv                    \
    --csv=./tests/fixtures/demo.csv             \
    --schema=./tests/schemas/demo_invalid.yml
```


### Complete CLI Help Message

Here you can see all available options and commands.  Tool uses [JBZoo/Cli](https://github.com/JBZoo/Cli) package for the CLI interface.
So there are options here for all occasions.


<!-- validate-csv-help -->
```
./csv-blueprint validate:csv --help


Description:
  Validate CSV file(s) by schema.

Usage:
  validate:csv [options]

Options:
  -c, --csv=CSV                    Path(s) to validate.
                                   You can specify path in which CSV files will be searched (max depth is 10).
                                   Feel free to use glob pattrens. Usage examples:
                                   /full/path/file.csv, p/file.csv, p/*.csv, p/**/*.csv, p/**/name-*.csv, **/*.csv, etc. (multiple values allowed)
  -s, --schema=SCHEMA              Schema filepath.
                                   It can be a YAML, JSON or PHP. See examples on GitHub.
  -r, --report=REPORT              Report output format. Available options:
                                   text, table, github, gitlab, teamcity, junit [default: "table"]
  -Q, --quick[=QUICK]              Immediately terminate the check at the first error found.
                                   Of course it will speed up the check, but you will get only 1 message out of many.
                                   If any error is detected, the utility will return a non-zero exit code.
                                   Empty value or "yes" will be treated as "true". [default: "no"]
  -S, --skip-schema[=SKIP-SCHEMA]  Skip schema validation.
                                   If you are sure that the schema is correct, you can skip this check.
                                   Empty value or "yes" will be treated as "true". [default: "no"]
      --no-progress                Disable progress bar animation for logs. It will be used only for text output format.
      --mute-errors                Mute any sort of errors. So exit code will be always "0" (if it's possible).
                                   It has major priority then --non-zero-on-error. It's on your own risk!
      --stdout-only                For any errors messages application will use StdOut instead of StdErr. It's on your own risk!
      --non-zero-on-error          None-zero exit code on any StdErr message.
      --timestamp                  Show timestamp at the beginning of each message.It will be used only for text output format.
      --profile                    Display timing and memory usage information.
      --output-mode=OUTPUT-MODE    Output format. Available options:
                                   text - Default text output format, userfriendly and easy to read.
                                   cron - Shortcut for crontab. It's basically focused on human-readable logs output.
                                   It's combination of --timestamp --profile --stdout-only --no-progress -vv.
                                   logstash - Logstash output format, for integration with ELK stack.
                                    [default: "text"]
      --cron                       Alias for --output-mode=cron. Deprecated!
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```
<!-- /validate-csv-help -->


### Report examples

As a result of the validation process, you will receive a human-readable table with a list of errors found in the CSV file. By defualt, the output format is a table, but you can choose from a variety of formats, such as text, GitHub, GitLab, TeamCity, JUnit, and more.  For example, the following output is generated using the `table` format.

Default report format is `table`:

<!-- output-table -->
```
./csv-blueprint validate:csv --csv='./tests/fixtures/demo.csv' --schema='./tests/schemas/demo_invalid.yml'


Schema: ./tests/schemas/demo_invalid.yml
Found CSV files: 1

Schema is invalid: ./tests/schemas/demo_invalid.yml
+-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+
| Line  | id:Column        | Rule         | Message                                                              |
+-------+------------------+--------------+----------------------------------------------------------------------+
| undef | 2:Float          | is_float     | Value "Qwerty" is not a float number                                 |
| undef | 4:Favorite color | allow_values | Value "123" is not allowed. Allowed values: ["red", "green", "Blue"] |
+-------+------------------+--------------+----- demo_invalid.yml -----------------------------------------------+

(1/1) Invalid file: ./tests/fixtures/demo.csv
+-------+------------------+------------------+----------------------- demo.csv ---------------------------------------------------------------------+
| Line  | id:Column        | Rule             | Message                                                                                              |
+-------+------------------+------------------+------------------------------------------------------------------------------------------------------+
| undef |                  | filename_pattern | Filename "./tests/fixtures/demo.csv" does not match pattern: "/demo-[12].csv$/i"                     |
| 1     |                  | csv.header       | Columns not found in CSV: "wrong_column_name"                                                        |
| 6     | 0:Name           | length_min       | The length of the value "Carl" is 4, which is less than the expected "5"                             |
| 11    | 0:Name           | length_min       | The length of the value "Lois" is 4, which is less than the expected "5"                             |
| 1     | 1:City           | ag:is_unique     | Column has non-unique values. Unique: 9, total: 10                                                   |
| 2     | 2:Float          | num_max          | The number of the value "4825.185", which is greater than the expected "4825.184"                    |
| 6     | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
|       |                  |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
| 8     | 3:Birthday       | date_min         | The date of the value "1955-05-14" is parsed as "1955-05-14 00:00:00 +00:00", which is less than the |
|       |                  |                  | expected "1955-05-15 00:00:00 +00:00 (1955-05-15)"                                                   |
| 9     | 3:Birthday       | date_max         | The date of the value "2010-07-20" is parsed as "2010-07-20 00:00:00 +00:00", which is greater than  |
|       |                  |                  | the expected "2009-01-01 00:00:00 +00:00 (2009-01-01)"                                               |
| 5     | 4:Favorite color | allow_values     | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"]                                |
+-------+------------------+------------------+----------------------- demo.csv ---------------------------------------------------------------------+


Found 10 issues in CSV file.
Found 2 issues in schema.

```
<!-- /output-table -->


Optional format `text` with highlited keywords:
```sh
./csv-blueprint validate:csv --report=text
```


![Report - Text](.github/assets/output-text.png)


**Notes**
* Report format for GitHub Actions is `github` by default.
* Tools uses [JBZoo/CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) as SDK to convert reports to different formats. So you can easily integrate it with any CI system.


## Coming soon

It's random ideas and plans. No orderings and deadlines. <u>But batch processing is the priority #1</u>.

* **Batch processing**
  * If option `--csv` is not specified, then the STDIN is used. To build a pipeline in Unix-like systems.
  * Discovering CSV files by `filename_pattern` in the schema file. In case you have a lot of schemas and a lot of CSV files and want to automate the process as one command.
  * Flag to ignore file name pattern. It's useful when you have a lot of files, and you don't want to validate the file name.

* **Validation**
  * [More aggregate rules](https://github.com/markrogoyski/math-php#statistics---descriptive).
  * [More cell rules](https://github.com/Respect/Validation).
  * `required` flag for the column.
  * Multi values in one cell.
  * Custom cell rule as a callback. It's useful when you have a complex rule that can't be described in the schema file.
  * Custom agregate rule as a callback. It's useful when you have a complex rule that can't be described in the schema file.
  * Configurable keyword for null/empty values. By default, it's an empty string. But you will use `null`, `nil`, `none`, `empty`, etc. Overridable on the column level.
  * Handle empty files and files with only a header row, or only with one line of data. One column wthout header is also possible.
  * Using multiple schemas for one csv file.
  * Inheritance of schemas, rules and columns. Define parent schema and override some rules in the child schemas. Make it DRY and easy to maintain.
  * If option `--schema` is not specified, then validate only super base level things (like "is it a CSV file?").
  * Complex rules (like "if field `A` is not empty, then field `B` should be not empty too").
  * Extending with custom rules and custom report formats. Plugins?
  * Input encoding detection + `BOM` (right now it's experimental). It works but not so accurate... UTF-8/16/32 is the best choice for now.

* **Release workflow**
  * Build and release Docker image [via GitHub Actions, tags and labels](https://docs.docker.com/build/ci/github-actions/manage-tags-labels/). Review it.
  * Build phar file and release via GitHub Actions.
  * Auto insert tool version into the Docker image and phar file. It's important to know the version of the tool you are using.
  * Show version as part of output.

* **Performance and optimization**
  * Benchmarks as part of the CI(?) and Readme. It's important to know how much time the validation process takes.
  * Optimization on `php.ini` level to start it faster. JIT, opcache, preloading, etc.
  * Parallel validation of really-really large files (1GB+ ?). I know you have them and not so much memory.
  * Parallel validation of multiple files at once.

* **Mock data generation**
  * Create CSV files based on the schema (like "create 1000 rows with random data based on schema and rules").
  * Use [Faker](https://github.com/FakerPHP/Faker) for random data generation.
  * [ReverseRegex](https://github.com/enso-media/ReverseRegex) to generate text from regex.

* **Reporting**
  * More report formats (like JSON, XML, etc). Any ideas?
  * Gitlab and JUnit reports must be as one structure. It's not so easy to implement. But it's a good idea.
  * Merge reports from multiple CSV files into one report. It's useful when you have a lot of files and you want to see all errors in one place. Especially for GitLab and JUnit reports.

* **Misc**
  * Use it as PHP SDK. Examples in Readme.
  * Warnings about deprecated options and features.
  * Warnings about invalid schema files.
  * Move const:HELP to PHP annotations. Canonic way to describe the command.
  * S3 Storage support. Validate files in the S3 bucket?
  * More examples and documentation.

 
PS. [There is a file](tests/schemas/todo.yml) with my ideas and imagination. It's not valid schema file, just a draft.
I'm not sure if I will implement all of them. But I will try to do my best.


## Disadvantages?

It is perceived that PHP is a slow language. I don't agree with that. You just need to know how to prepare it.
See [Processing One Billion CSV rows in PHP!](https://dev.to/realflowcontrol/processing-one-billion-rows-in-php-3eg0).
That is, if you do everything right, you can read, aggregate and calculate data from CSV at **~15 million lines per second**!

* Yeah-yeah. I know it's not the fastest tool in the world. But it's not the slowest either. See link above.
* Yeah-yeah. I know it's PHP (not Python, Go, PySpark...). PHP is not the best language for such tasks.
* Yeah-yeah. It looks like a standalone binary. Right. Just use it, don't think about how it works.
* Yeah-yeah. I know you can't use as Python SDK as part of a pipeline.

But... it's not a problem for most cases. And it solves the problem of validating CSV files in CI. 👍

The utility is made to just pick up and use and not think about how it works internally.
Moreover, everything is covered as strictly as possible by tests, strict typing of variables + `~7` linters and static analyzers (max level of rules). 
Also, if you look, you'll see that any PR goes through about `~30` different checks on GitHub Actions (matrix of PHP versions and mods).
Since I don't know under what conditions the code will be used, everything I can think of is covered. The wonderful world of Open Source.

So... as strictly as possible in today's PHP world. I think it works as expected.


## Interesting fact

I've set a personal record. The first version was written from scratch in about 3 days (with really frequent breaks to take care of 4 month baby).
I'm looking at the first commit and the very first git tag. I'd say over the weekend, in my spare time on my personal laptop.
Well... AI was only used for this Readme file because I'm not very good at English. 🤔

I seem to be typing fast and I had really great inspiration. I hope my wife doesn't divorce me. 😅


## Contributing
If you have any ideas or suggestions, feel free to open an issue or create a pull request.

```sh
# Fork the repo and build project
git clone git@github.com:jbzoo/csv-blueprint.git ./jbzoo-csv-blueprint
cd ./jbzoo-csv-blueprint
make build

# Make your local changes

# Autofix code style 
make test-phpcsfixer-fix

# Run all tests and check code style
make test
make codestyle

# Create your pull request and check all tests in CI (Github Actions)
# ???
# Profit!
```


## License

[MIT License](LICENSE): It's like free pizza - enjoy it, share it, just don't sell it as your own. And remember, no warranty for stomach aches! 😅


## See Also

- [Cli](https://github.com/JBZoo/Cli) - Framework helps create complex CLI apps and provides new tools for Symfony/Console.
- [CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) - It converts different error reporting standards for popular CI systems.
- [Composer-Diff](https://github.com/JBZoo/Composer-Diff) - See what packages have changed after `composer update`.
- [Composer-Graph](https://github.com/JBZoo/Composer-Graph) - Dependency graph visualization of `composer.json` based on [Mermaid JS](https://mermaid.js.org/).
- [Mermaid-PHP](https://github.com/JBZoo/Mermaid-PHP) - Generate diagrams and flowcharts with the help of the [mermaid](https://mermaid.js.org/) script language.
- [Utils](https://github.com/JBZoo/Utils) - Collection of useful PHP functions, mini-classes, and snippets for every day.
- [Image](https://github.com/JBZoo/Image) - Package provides object-oriented way to manipulate with images as simple as possible.
- [Data](https://github.com/JBZoo/Data) - Extended implementation of ArrayObject. Use Yml/PHP/JSON/INI files as config. Forget about arrays.
- [Retry](https://github.com/JBZoo/Retry) - Tiny PHP library providing retry/backoff functionality with strategies and jitter.
