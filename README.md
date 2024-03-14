# JBZoo / Csv-Blueprint

[![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml?query=branch%3Amaster)    [![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/release-docker.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Csv-Blueprint/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Csv-Blueprint?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Csv-Blueprint/coverage.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Csv-Blueprint/level.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/badge)](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/csv-blueprint/version)](https://packagist.org/packages/jbzoo/csv-blueprint/)    [![Total Downloads](https://poser.pugx.org/jbzoo/csv-blueprint/downloads)](https://packagist.org/packages/jbzoo/csv-blueprint/stats)    [![Docker Pulls](https://img.shields.io/docker/pulls/jbzoo/csv-blueprint.svg)](https://hub.docker.com/r/jbzoo/csv-blueprint)    [![Dependents](https://poser.pugx.org/jbzoo/csv-blueprint/dependents)](https://packagist.org/packages/jbzoo/csv-blueprint/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/csv-blueprint)](https://github.com/JBZoo/Csv-Blueprint/blob/master/LICENSE)


## Introduction

The JBZoo/Csv-Blueprint tool is a powerful and flexible utility designed for validating CSV files against 
a predefined schema specified in YAML format. With the capability to run both locally and in Docker environments,
JBZoo/Csv-Blueprint is an ideal choice for integrating into CI/CD pipelines, such as GitHub Actions,
to ensure the integrity of CSV data in your projects.


## Why validate CSV files in CI?

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
* **Various ways to report:** issues that can be easily integrated with GithHub, Gitlab, TeamCity, etc. The default output is a human-readable table. [See Live Demo](https://github.com/JBZoo/Csv-Blueprint-Demo).


## Live Demo

* As a live demonstration of how the tool works you can take a look at the super minimal repository - [JBZoo/Csv-Blueprint-Demo](https://github.com/JBZoo/Csv-Blueprint-Demo). Feel free to fork it and play with the tool.
* You can see more complex examples and different ways of reporting by looking at the [last Demo pipeline](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml?query=branch%3Amaster) (please open the logs). There you'll find the basic ways to get started. And also the `All Report Types` (left sidebar) link with the different report types.

**See also**
* [PR as a live demo](https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files)
* [.github/workflows/demo.yml](.github/workflows/demo.yml)
* [demo_invalid.yml](tests/schemas/demo_invalid.yml)
* [demo_valid.yml](tests/schemas/demo_valid.yml)
* [demo.csv](tests/fixtures/demo.csv)


## Usage

Also see demo in the [GitHub Actions](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml) file.


### As GitHub Action

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

```

**Note**. Report format for GitHub Actions is `github` by default. See [GitHub Actions friendly](https://docs.github.com/en/actions/using-workflows/workflow-commands-for-github-actions#setting-a-warning-message) and [PR as a live demo](https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files). 

This allows you to see bugs in the GitHub interface at the PR level.
That is, the error will be shown in a specific place in the CSV file right in diff of your Pull Requests! [See example]((https://github.com/JBZoo/Csv-Blueprint-Demo/pull/1/files).

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
    -v .:/parent-host                            \
    jbzoo/csv-blueprint                          \
    validate:csv                                 \
    --csv=./tests/fixtures/demo.csv              \
    --schema=./tests/schemas/demo_invalid.yml
```


### As PHP binary
Ensure you have PHP installed on your machine.

**Status: WIP**. It's not released yet. But you can build it from source. See manual above and `./build//csv-blueprint.phar` file.

```sh
wget https://github.com/JBZoo/Csv-Blueprint/releases/latest/download/csv-blueprint.phar
chmod +x ./csv-blueprint.phar
./csv-blueprint.phar validate:csv               \
   --csv=./tests/fixtures/demo.csv              \
   --schema=./tests/schemas/demo_invalid.yml
```


### As PHP project
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


### CLI Help Message

Here you can see all available options and commands.  Tool uses [JBZoo/Cli](https://github.com/JBZoo/Cli) package for the CLI interface.
So there are options here for all occasions.


```
./csv-blueprint validate:csv --help


Description:
  Validate CSV file(s) by schema.

Usage:
  validate:csv [options]

Options:
  -c, --csv=CSV                  Path(s) to validate.
                                 You can specify path in which CSV files will be searched (max depth is 10).
                                 Feel free to use glob pattrens. Usage examples:
                                 /full/path/file.csv, p/file.csv, p/*.csv, p/**/*.csv, p/**/name-*.csv, **/*.csv, etc. (multiple values allowed)
  -s, --schema=SCHEMA            Schema filepath.
                                 It can be a YAML, JSON or PHP. See examples on GitHub.
  -r, --report=REPORT            Report output format. Available options:
                                 text, table, github, gitlab, teamcity, junit [default: "table"]
  -Q, --quick[=QUICK]            Immediately terminate the check at the first error found.
                                 Of course it will speed up the check, but you will get only 1 message out of many.
                                 If any error is detected, the utility will return a non-zero exit code.
                                 Empty value or "yes" will be treated as "true". [default: "no"]
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


### Report examples

As a result of the validation process, you will receive a human-readable table with a list of errors found in the CSV file. By defualt, the output format is a table, but you can choose from a variety of formats, such as text, GitHub, GitLab, TeamCity, JUnit, and more.  For example, the following output is generated using the "table" format.

**Notes**
* Report format for GitHub Actions is `github` by default.
* Tools uses [JBZoo/CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) as SDK to convert reports to different formats. So you can easily integrate it with any CI system.


Default report format is `table`:

```
./csv-blueprint validate:csv --csv='./tests/fixtures/batch/*.csv' --schema='./tests/schemas/demo_invalid.yml'


Schema: ./tests/schemas/demo_invalid.yml
Found CSV files: 3

(1/3) Invalid file: ./tests/fixtures/batch/demo-1.csv
+------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+
| Line | id:Column        | Rule         | Message                                                               |
+------+------------------+--------------+-----------------------------------------------------------------------+
| 3    | 2:Float          | max          | Value "74605.944" is greater than "74605"                             |
| 3    | 4:Favorite color | allow_values | Value "blue" is not allowed. Allowed values: ["red", "green", "Blue"] |
+------+------------------+--------------+--------- demo-1.csv --------------------------------------------------+

(2/3) Invalid file: ./tests/fixtures/batch/demo-2.csv
+------+------------+------------+------------------ demo-2.csv ----------------------------------------------------+
| Line | id:Column  | Rule       | Message                                                                          |
+------+------------+------------+----------------------------------------------------------------------------------+
| 2    | 0:Name     | min_length | Value "Carl" (length: 4) is too short. Min length is 5                           |
| 2    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
| 4    | 3:Birthday | min_date   | Value "1955-05-14" is less than the minimum date "1955-05-15T00:00:00.000+00:00" |
| 5    | 3:Birthday | max_date   | Value "2010-07-20" is more than the maximum date "2009-01-01T00:00:00.000+00:00" |
| 7    | 0:Name     | min_length | Value "Lois" (length: 4) is too short. Min length is 5                           |
+------+------------+------------+------------------ demo-2.csv ----------------------------------------------------+

(3/3) Invalid file: ./tests/fixtures/batch/sub/demo-3.csv
+------+-----------+------------------+---------------------- demo-3.csv ------------------------------------------------------------+
| Line | id:Column | Rule             | Message                                                                                      |
+------+-----------+------------------+----------------------------------------------------------------------------------------------+
| 0    |           | filename_pattern | Filename "./tests/fixtures/batch/sub/demo-3.csv" does not match pattern: "/demo-[12].csv$/i" |
+------+-----------+------------------+---------------------- demo-3.csv ------------------------------------------------------------+


Found 8 issues in 3 out of 3 CSV files.

```


Optional format `text` with highlited keywords:
```sh
./csv-blueprint validate:csv --report=text
```

![Report - Text](.github/assets/output-text.png)


### Schema Definition
Define your CSV validation schema in a YAML file.

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

In the [example Yml file](schema-examples/full.yml) you can find a detailed description of all features.
It's also covered by tests, so it's always up-to-date.

**Important notes**
* I have deliberately refused typing of columns (like `type: integer`) and replaced them with rules,
which can be combined in any sequence and completely at your discretion.
This gives you great flexibility when validating CSV files.
* All fields (unless explicitly stated otherwise) are optional, and you can choose not to declare them. Up to you.
* You are always free to add your option anywhere (except the `rules` list) and it will be ignored. I find it convenient for additional integrations and customization.


### Schema file examples

Available formats: [YAML](schema-examples/full.yml), [JSON](schema-examples/full.json), [PHP](schema-examples/full.php).

```yml
# It's a full example of the CSV schema file in YAML format.

# Regular expression to match the file name. If not set, then no pattern check
# This way you can validate the file name before the validation process.
# Feel free to check parent directories as well.
filename_pattern: /demo(-\d+)?\.csv$/i

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
      word_count: 10                    # Integer only. Exact count of words in the string. Example: "Hello World, 123" - 2 words only (123 is not a word)
      min_word_count: 1                 # Integer only. Min count of words in the string. Example: "Hello World. 123" - 2 words only (123 is not a word)
      max_word_count: 5                 # Integer only. Max count of words in the string Example: "Hello World! 123" - 2 words only (123 is not a word)
      at_least_contains: [ a, b ]       # At least one of the string must be in the CSV value. Case-sensitive.
      all_must_contain: [ a, b, c ]     # All the strings must be part of a CSV value. Case-sensitive.
      starts_with: "prefix "            # Case-sensitive. Example: "prefix Hello World"
      ends_with: " suffix"              # Case-sensitive. Example: "Hello World suffix"

      # Decimal and integer numbers
      min: 10                           # Can be integer or float, negative and positive
      max: 100.50                       # Can be integer or float, negative and positive
      precision: 3                      # Strict(!) number of digits after the decimal point
      min_precision: 2                  # Min number of digits after the decimal point (with zeros)
      max_precision: 4                  # Max number of digits after the decimal point (with zeros)

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
      is_alias: true                    # Only alias format. Example: "my-alias-123"
      cardinal_direction: true          # Valid cardinal direction. Examples: "N", "S", "NE", "SE", "none", ""
      usa_market_name: true             # Check if the value is a valid USA market name. Example: "New York, NY"

  - name: "another_column"

```


## Coming soon

It's random ideas and plans. No orderings and deadlines. <u>But batch processing is the priority #1</u>.

Batch processing
* [x] ~~CSV/Schema file discovery in the folder with regex filename pattern (like `glob(./**/dir/*.csv)`).~~
* [x] ~~If option `--csv` is a folder, then validate all files in the folder.~~
* [x] ~~Checking multiple CSV files in one schema.~~
* [x] ~~Quick stop flag. If the first error is found, then stop the validation process to save time.~~
* [ ] If option `--csv` is not specified, then the STDIN is used. To build a pipeline in Unix-like systems.
* [ ] Discovering CSV files by `filename_pattern` in the schema file. In case you have a lot of schemas and a lot of CSV files and want to automate the process as one command.

Validation
* [x] ~~`filename_pattern` validation with regex (like "all files in the folder should be in the format `/^[\d]{4}-[\d]{2}-[\d]{2}\.csv$/`").~~
* [ ] Flag to ignore file name pattern. It's useful when you have a lot of files and you don't want to validate the file name.
* [ ] Keyword for null value. Configurable. By default, it's an empty string. But you can use `null`, `nil`, `none`, `empty`, etc. Overridable on the column level.
* [ ] Agregate rules (like "at least one of the fields should be not empty" or "all values must be unique").
* [ ] Handle empty files and files with only a header row, or only with one line of data. One column wthout header is also possible.
* [ ] Using multiple schemas for one csv file.
* [ ] Inheritance of schemas, rules and columns. Define parent schema and override some rules in the child schemas. Make it DRY and easy to maintain.
* [ ] Validate syntax and options in the schema file. It's important to know if the schema file is valid and can be used for validation.
* [ ] If option `--schema` is not specified, then validate only super base level things (like "is it a CSV file?").
* [ ] Complex rules (like "if field `A` is not empty, then field `B` should be not empty too").
* [ ] Extending with custom rules and custom report formats. Plugins?
* [ ] Input encoding detection + `BOM` (right now it's experimental). It works but not so accurate... UTF-8/16/32 is the best choice for now.

Release workflow
* [ ] Build and release Docker image [via GitHub Actions, tags and labels](https://docs.docker.com/build/ci/github-actions/manage-tags-labels/). Review it.
* [ ] Upgrading to PHP 8.3.x
* [ ] Build phar file and release via GitHub Actions.
* [ ] Auto insert tool version into the Docker image and phar file. It's important to know the version of the tool you are using.
* [ ] Show version as part of output.

Performance and optimization
* [ ] Parallel validation of really-really large files (1GB+ ?). I know you have them and not so much memory.
* [ ] Parallel validation of multiple files at once.
* [ ] Benchmarks as part of the CI(?) and Readme. It's important to know how much time the validation process takes.
* [ ] Optimazation on `php.ini` level to start it faster. JIT.

Mock data generation
* [ ] Create CSV files based on the schema (like "create 1000 rows with random data based on schema and rules").
* [ ] Use [Faker](https://github.com/FakerPHP/Faker) for random data generation.

Reporting
* [ ] Fix auto width of tables in Githu terminal.
* [ ] 
* [ ] More report formats (like JSON, XML, etc). Any ideas?
* [ ] Gitlab and JUnit reports must be as one structure. It's not so easy to implement. But it's a good idea.
* [ ] Merge reports from multiple CSV files into one report. It's useful when you have a lot of files and you want to see all errors in one place. Especially for GitLab and JUnit reports.

Misc
* [ ] Use it as PHP SDK. Examples in Readme.
* [ ] S3 Storage support. Validate files in the S3 bucket?
* [ ] More examples and documentation.

 
PS. [There is a file](tests/schemas/example_full.yml) with my ideas and imagination.
I'm not sure if I will implement all of them. But I will try to do my best.


## Disadvantages?

* Yeah-yeah. I know it's not the fastest tool in the world. But it's not the slowest either.
* Yeah-yeah. I know it's PHP (not a Python, Go). PHP is not the best language for such tasks.
* Yeah-yeah. It looks like a standalone binary.
* Yeah-yeah. I know you can't use as Python SDK as part of pipeline.

But... it's not a problem for most cases. And it solves the problem of validating CSV files in CI. 👍

The utility is made to just pick up and use and not think about how it works internally.
Moreover, everything is covered as strictly as possible by tests, strict typing of variables + `~7` linters and static analyzers (max level of rules). 
Also, if you look, you'll see that any PR goes through about `~30` different checks on GitHub Actions (matrix of PHP versions and mods).
Since I don't know under what conditions the code will be used, everything I can think of is covered. The wonderful world of Open Source.

So... as strictly as possible in today's PHP world. I think it works as expected.


## Interesting fact

I think I've set a personal record. 
The first version was written from scratch in about 3 days (with really frequent breaks to take care of 4 month baby).
I'm looking at the first commit and the very first git tag. I'd say over the weekend, in my spare time on my personal laptop.
Well... AI I only used for this Readme file because I'm not very good at English. 🤔

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

- [CI-Report-Converter](https://github.com/JBZoo/CI-Report-Converter) - It converts different error reporting standards for popular CI systems.
- [Composer-Diff](https://github.com/JBZoo/Composer-Diff) - See what packages have changed after `composer update`.
- [Composer-Graph](https://github.com/JBZoo/Composer-Graph) - Dependency graph visualization of `composer.json` based on [Mermaid JS](https://mermaid.js.org/).
- [Mermaid-PHP](https://github.com/JBZoo/Mermaid-PHP) - Generate diagrams and flowcharts with the help of the [mermaid](https://mermaid.js.org/) script language.
- [Utils](https://github.com/JBZoo/Utils) - Collection of useful PHP functions, mini-classes, and snippets for every day.
- [Image](https://github.com/JBZoo/Image) - Package provides object-oriented way to manipulate with images as simple as possible.
- [Data](https://github.com/JBZoo/Data) - Extended implementation of ArrayObject. Use Yml/PHP/JSON/INI files as config. Forget about arrays.
- [Retry](https://github.com/JBZoo/Retry) - Tiny PHP library providing retry/backoff functionality with strategies and jitter.
