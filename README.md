# JBZoo / Csv-Blueprint

[![CI](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml/badge.svg?branch=master)](https://github.com/JBZoo/Csv-Blueprint/actions/workflows/main.yml?query=branch%3Amaster)    [![Coverage Status](https://coveralls.io/repos/github/JBZoo/Csv-Blueprint/badge.svg?branch=master)](https://coveralls.io/github/JBZoo/Csv-Blueprint?branch=master)    [![Psalm Coverage](https://shepherd.dev/github/JBZoo/Csv-Blueprint/coverage.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![Psalm Level](https://shepherd.dev/github/JBZoo/Csv-Blueprint/level.svg)](https://shepherd.dev/github/JBZoo/Csv-Blueprint)    [![CodeFactor](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/badge)](https://www.codefactor.io/repository/github/jbzoo/csv-blueprint/issues)    
[![Stable Version](https://poser.pugx.org/jbzoo/csv-blueprint/version)](https://packagist.org/packages/jbzoo/csv-blueprint/)    [![Total Downloads](https://poser.pugx.org/jbzoo/csv-blueprint/downloads)](https://packagist.org/packages/jbzoo/csv-blueprint/stats)    [![Dependents](https://poser.pugx.org/jbzoo/csv-blueprint/dependents)](https://packagist.org/packages/jbzoo/csv-blueprint/dependents?order_by=downloads)    [![GitHub License](https://img.shields.io/github/license/jbzoo/csv-blueprint)](https://github.com/JBZoo/Csv-Blueprint/blob/master/LICENSE)




### Installing

```sh
composer require jbzoo/csv-blueprint
```


### Usage


### Schema file examples

<details>
  <summary>Click to see YAML format</summary>

  ```yml
# It's a full example of the CSV schema file in YAML format.

csv_structure: # Here are default values. You can skip this section if you don't need to override the default values
  header: true                          # If the first row is a header. If true, name of each column is required
  delimiter: ,                          # Delimiter character in CSV file
  quote_char: \                         # Quote character in CSV file
  enclosure: "\""                       # Enclosure for each field in CSV file
  encoding: utf-8                       # Only utf-8, utf-16, utf-32 (Experimental)
  bom: false                            # If the file has a BOM (Byte Order Mark) at the beginning (Experimental)

columns:
  - name: "csv header name"
    description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
    rules:
      allow_values: [ y, n, "" ]        # Strict set of values that are allowed
      date_format: Y-m-d                # See: https://www.php.net/manual/en/datetime.format.php
      exact_value: Some string          # Case-sensitive. Exact value for string in the column
      is_bool: true                     # true|false, Case-insensitive
      is_domain: true                   # Only domain name. Example: "example.com"
      is_email: true                    # Only email format. Example: "user@example.com"
      is_float: true                    # Check format only. Can be negative and positive. Dot as decimal separator
      is_int: true                      # Check format only. Can be negative and positive. Without any separators
      is_ip: true                       # Only IPv4. Example: "127.0.0.1"
      is_latitude: true                 # Can be integer or float. Example: 50.123456
      is_longitude: true                # Can be integer or float. Example: -89.123456
      is_url: true                      # Only URL format. Example: "https://example.com/page?query=string#anchor"
      is_uuid4: true                    # Only UUID4 format. Example: "550e8400-e29b-41d4-a716-446655440000"
      min: 10                           # Can be integer or float, negative and positive
      max: 100                          # Can be integer or float, negative and positive
      min_length: 1                     # Integer only. Min length of the string with spaces
      max_length: 10                    # Integer only. Max length of the string with spaces
      min_date: "2000-01-02"            # See examples https://www.php.net/manual/en/function.strtotime.php
      max_date: now                     # See examples https://www.php.net/manual/en/function.strtotime.php
      not_empty: true                   # Value is not empty string. Ignore spaces.
      only_capitalize: true             # String is only capitalized. Example: "Hello World"
      only_lowercase: true              # String is only lowercase. Example: "hello world"
      only_uppercase: true              # String is only capitalized. Example: "HELLO WORLD"
      only_trimed: true                 # Only trimed strings. Example: "Hello World" (not " Hello World ")
      precision: 2                      # Strict(!) number of digits after the decimal point
      regex: /^[\d]{2}$/                # Any valid regex pattern. See https://www.php.net/manual/en/reference.pcre.pattern.syntax.php
      cardinal_direction: true          # Valid cardinal direction. Examples: "N", "S", "NE", "SE", "none", ""
      usa_market_name: true             # Check if the value is a valid USA market name. Example: "New York, NY"

```

</details>



## Unit tests and check code style
```sh
make update
make test-all
```


### License

MIT
