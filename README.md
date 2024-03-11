# JBZoo / Csv-Blueprint

[![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Csv-Blueprint/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Csv-Blueprint?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Csv-Blueprint/coverage.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Csv-Blueprint/level.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/badge)](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/csv-blueprint/version)](https://packagist.org/packages/jbzoo/csv-blueprint/)    [![Total Downloads](https://poser.pugx.org/jbzoo/csv-blueprint/downloads)](https://packagist.org/packages/jbzoo/csv-blueprint/stats)    [![Docker Pulls](https://img.shields.io/docker/pulls/jbzoo/csv-blueprint.svg)](https://hub.docker.com/r/jbzoo/csv-blueprint)    [![Dependents](https://poser.pugx.org/jbzoo/csv-blueprint/dependents)](https://packagist.org/packages/jbzoo/csv-blueprint/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/csv-blueprint)](https://github.com/JBZoo/Csv-Blueprint/blob/master/LICENSE)


<!--ts-->

<!--te-->


## Introduction
The JBZoo/Csv-Blueprint tool is a powerful and flexible utility designed for validating CSV files against a predefined schema specified in YAML format. With the capability to run both locally and in Docker environments, JBZoo/Csv-Blueprint is an ideal choice for integrating into CI/CD pipelines, such as GitHub Actions, to ensure the integrity of CSV data in your projects.


## Features
* **Schema-based Validation**: Define the structure and rules for your CSV files in an intuitive [YAML format](schema-examples/full.yml), enabling precise validation against your data's expected format.
* **Flexible Configuration**: Support for custom delimiters, quote characters, enclosures, and encoding settings to handle a wide range of CSV formats.
* **Comprehensive Rule Set**: Includes a broad set of validation rules, such as non-empty fields, exact values, regular expressions, numeric constraints, date formats, and more, catering to various data validation needs.
* **Docker Support**: Easily integrate into any workflow with Docker, providing a seamless experience for development, testing, and production environments.
* **GitHub Actions Integration**: Automate CSV validation in your CI/CD pipeline, enhancing the quality control of your data in pull requests and deployments.
* **Various ways to report** issues that can be easily integrated with GithHub, Gitlab, TeamCity, etc. The default output is a human-readable table. [See Live Demo](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/demo.yml).





## Installing

```sh
composer require jbzoo/csv-blueprint
```


## Usage

As Docker container:

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

### Schema Definition
Define your CSV validation schema in a YAML file.

This example defines a simple schema for a CSV file with a header row, specifying that the id column must not be empty and must contain integer values.
Also it checks that the name column is not empty and has a minimum length of 3 characters.

```yaml

Here's an example to get you started:
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
      not_empty: true
      min_length: 3

```

### Schema file examples

In the [example Yml file](schema-examples/full.yml) you can find a detailed description of all features.

**Important notes**
* I have deliberately refused typing of columns and replaced them with rules,
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


## Unit tests and check code style
```sh
make update
make test-all
```


### License

MIT
