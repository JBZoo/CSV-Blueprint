<?php

/**
 * JBZoo Toolbox - Csv-Blueprint.
 *
 * This file is part of the JBZoo Toolbox project.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @see        https://github.com/JBZoo/Csv-Blueprint
 */

declare(strict_types=1);

use JBZoo\CsvBlueprint\Validators\Rules\AllowValues;
use JBZoo\CsvBlueprint\Validators\Rules\DateFormat;
use JBZoo\CsvBlueprint\Validators\Rules\ExactValue;
use JBZoo\CsvBlueprint\Validators\Rules\IsBool;
use JBZoo\CsvBlueprint\Validators\Rules\IsDomain;
use JBZoo\CsvBlueprint\Validators\Rules\IsEmail;
use JBZoo\CsvBlueprint\Validators\Rules\IsFloat;
use JBZoo\CsvBlueprint\Validators\Rules\IsInt;
use JBZoo\CsvBlueprint\Validators\Rules\IsIp;
use JBZoo\CsvBlueprint\Validators\Rules\IsLatitude;
use JBZoo\CsvBlueprint\Validators\Rules\IsLongitude;
use JBZoo\CsvBlueprint\Validators\Rules\IsUrl;
use JBZoo\CsvBlueprint\Validators\Rules\Max;
use JBZoo\CsvBlueprint\Validators\Rules\MaxDate;
use JBZoo\CsvBlueprint\Validators\Rules\MaxLength;
use JBZoo\CsvBlueprint\Validators\Rules\Min;
use JBZoo\CsvBlueprint\Validators\Rules\MinDate;
use JBZoo\CsvBlueprint\Validators\Rules\MinLength;
use JBZoo\CsvBlueprint\Validators\Rules\NotEmpty;
use JBZoo\CsvBlueprint\Validators\Rules\OnlyCapitalize;
use JBZoo\CsvBlueprint\Validators\Rules\OnlyLowercase;
use JBZoo\CsvBlueprint\Validators\Rules\OnlyUppercase;
use JBZoo\CsvBlueprint\Validators\Rules\Precision;
use JBZoo\CsvBlueprint\Validators\Rules\Regex;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\PHPUnit\isSame;

final class RulesTest extends PHPUnit
{
    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testAllowValues(): void
    {
        $rule = new AllowValues('prop', ['1', '2', '3']);
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('2'));
        isSame(null, $rule->validate('3'));
        isSame(
            'Error "allow_values" at line 0, column "prop". ' .
            'Value "" is not allowed. Allowed values: ["1", "2", "3"].',
            $rule->validate(''),
        );
        isSame(
            'Error "allow_values" at line 0, column "prop". ' .
            'Value "invalid" is not allowed. Allowed values: ["1", "2", "3"].',
            $rule->validate('invalid'),
        );

        $rule = new AllowValues('prop', ['1', '2', '3', '']);
        isSame(null, $rule->validate(''));

        $rule = new AllowValues('prop', ['1', '2', '3', ' ']);
        isSame(null, $rule->validate(' '));
    }

    public function testDateFormat(): void
    {
        $rule = new DateFormat('prop', 'Y-m-d');
        isSame(null, $rule->validate('2000-12-31'));
        isSame(
            'Error "date_format" at line 0, column "prop". ' .
            'Date format of value "" is not valid. Expected format: "Y-m-d".',
            $rule->validate(''),
        );
        isSame(
            'Error "date_format" at line 0, column "prop". ' .
            'Date format of value "2000-01-02 12:34:56" is not valid. Expected format: "Y-m-d".',
            $rule->validate('2000-01-02 12:34:56'),
        );
    }

    public function testExactValue(): void
    {
        $rule = new ExactValue('prop', '123');
        isSame(null, $rule->validate('123'));
        isSame(
            'Error "exact_value" at line 0, column "prop". Value "" is not strict equal to "123".',
            $rule->validate(''),
        );
        isSame(
            'Error "exact_value" at line 0, column "prop". Value "2000-01-02" is not strict equal to "123".',
            $rule->validate('2000-01-02'),
        );
    }

    public function testIsBool(): void
    {
        $rule = new IsBool('prop');
        isSame(null, $rule->validate('true'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('TRUE'));
        isSame(null, $rule->validate('FALSE'));
        isSame(null, $rule->validate('True'));
        isSame(null, $rule->validate('False'));
        isSame(
            'Error "is_bool" at line 0, column "prop". Value "" is not allowed. Allowed values: ["true", "false"].',
            $rule->validate(''),
        );
        isSame(
            'Error "is_bool" at line 0, column "prop". Value "1" is not allowed. Allowed values: ["true", "false"].',
            $rule->validate('1'),
        );
    }

    public function testIsDomain(): void
    {
        $rule = new IsDomain('prop');
        isSame(null, $rule->validate('example.com'));
        isSame(null, $rule->validate('sub.example.com'));
        isSame(null, $rule->validate('sub.sub.example.com'));
        isSame(null, $rule->validate('sub.sub-example.com'));
        isSame(null, $rule->validate('sub-sub-example.com'));
        isSame(null, $rule->validate('sub-sub-example.qwerty'));
        isSame(
            'Error "is_domain" at line 0, column "prop". Value "example" is not a valid domain.',
            $rule->validate('example'),
        );
        isSame(
            'Error "is_domain" at line 0, column "prop". Value "" is not a valid domain.',
            $rule->validate(''),
        );
    }

    public function testIsEmail(): void
    {
        $rule = new IsEmail('prop');
        isSame(null, $rule->validate('user@example.com'));
        isSame(null, $rule->validate('user@sub.example.com'));
        isSame(
            'Error "is_email" at line 0, column "prop". Value "user:pass@example.com" is not a valid email.',
            $rule->validate('user:pass@example.com'),
        );
    }

    public function testIsFloat(): void
    {
        $rule = new IsFloat('prop');
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('1.0'));
        isSame(null, $rule->validate('-1'));
        isSame(null, $rule->validate('-1.0'));
        isSame(
            'Error "is_float" at line 0, column "prop". Value "1.000.000" is not a float number.',
            $rule->validate('1.000.000'),
        );
        isSame(
            'Error "is_float" at line 0, column "prop". Value "" is not a float number.',
            $rule->validate(''),
        );
        isSame(
            'Error "is_float" at line 0, column "prop". Value " 1" is not a float number.',
            $rule->validate(' 1'),
        );
    }

    public function testIsInt(): void
    {
        $rule = new IsInt('prop');
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('-1'));
        isSame(
            'Error "is_int" at line 0, column "prop". Value "1.000.000" is not an integer.',
            $rule->validate('1.000.000'),
        );
        isSame(
            'Error "is_int" at line 0, column "prop". Value "1.1" is not an integer.',
            $rule->validate('1.1'),
        );
        isSame(
            'Error "is_int" at line 0, column "prop". Value "1.0" is not an integer.',
            $rule->validate('1.0'),
        );
        isSame(
            'Error "is_int" at line 0, column "prop". Value "" is not an integer.',
            $rule->validate(''),
        );
        isSame(
            'Error "is_int" at line 0, column "prop". Value " 1" is not an integer.',
            $rule->validate(' 1'),
        );
        isSame(
            'Error "is_int" at line 0, column "prop". Value "1 " is not an integer.',
            $rule->validate('1 '),
        );
    }

    public function testIsIp(): void
    {
        $rule = new IsIp('prop');
        isSame(null, $rule->validate('127.0.0.1'));
        isSame(null, $rule->validate('0.0.0.0'));
        isSame(
            'Error "is_ip" at line 0, column "prop". Value "1.2.3" is not a valid IP.',
            $rule->validate('1.2.3'),
        );
    }

    public function testIsLatitude(): void
    {
        $rule = new IsLatitude('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('90'));
        isSame(null, $rule->validate('-90'));
        isSame(
            'Error "is_latitude" at line 0, column "prop". Value "123" is not a valid latitude (-90 <= x <= 90).',
            $rule->validate('123'),
        );
        isSame(
            'Error "is_latitude" at line 0, column "prop". Value "90.1" is not a valid latitude (-90 <= x <= 90).',
            $rule->validate('90.1'),
        );
        isSame(
            'Error "is_latitude" at line 0, column "prop". Value "90.1.1.1.1" is not a float number.',
            $rule->validate('90.1.1.1.1'),
        );
    }

    public function testIsLongitude(): void
    {
        $rule = new IsLongitude('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('180'));
        isSame(null, $rule->validate('-180'));
        isSame(
            'Error "is_longitude" at line 0, column "prop". Value "1230" is not a valid longitude (-180 <= x <= 180).',
            $rule->validate('1230'),
        );
        isSame(
            'Error "is_longitude" at line 0, column "prop". ' .
            'Value "180.0001" is not a valid longitude (-180 <= x <= 180).',
            $rule->validate('180.0001'),
        );
        isSame(
            'Error "is_longitude" at line 0, column "prop". Value "-180.1" is not a valid longitude (-180 <= x <= 180).',
            $rule->validate('-180.1'),
        );
        isSame(
            'Error "is_longitude" at line 0, column "prop". Value "1.0.0.0" is not a float number.',
            $rule->validate('1.0.0.0'),
        );
    }

    public function testIsUrl(): void
    {
        $rule = new IsUrl('prop');
        isSame(null, $rule->validate('http://example.com'));
        isSame(null, $rule->validate('http://example.com/home-page'));
        isSame(null, $rule->validate('ftp://user:pass@example.com/home-page?param=value&v=asd#anchor'));
        isSame(
            'Error "is_url" at line 0, column "prop". Value "123" is not a valid URL.',
            $rule->validate('123'),
        );
        isSame(
            'Error "is_url" at line 0, column "prop". Value "//example.com" is not a valid URL.',
            $rule->validate('//example.com'),
        );
    }

    public function testMin(): void
    {
        $rule = new Min('prop', 10);
        isSame(null, $rule->validate('10'));
        isSame(null, $rule->validate('11'));
        isSame(
            'Error "min" at line 0, column "prop". Value "9" is less than "10".',
            $rule->validate('9'),
        );
        isSame(
            'Error "min" at line 0, column "prop". Value "example.com" is not a float number.',
            $rule->validate('example.com'),
        );

        $rule = new Min('prop', 10.1);
        isSame(null, $rule->validate('10.1'));
        isSame(null, $rule->validate('11'));
        isSame(
            'Error "min" at line 0, column "prop". Value "9" is less than "10.1".',
            $rule->validate('9'),
        );
        isSame(
            'Error "min" at line 0, column "prop". Value "example.com" is not a float number.',
            $rule->validate('example.com'),
        );
    }

    public function testMax(): void
    {
        $rule = new Max('prop', 10);
        isSame(null, $rule->validate('9'));
        isSame(null, $rule->validate('10'));
        isSame(
            'Error "max" at line 0, column "prop". Value "123" is greater than "10".',
            $rule->validate('123'),
        );
        isSame(
            'Error "max" at line 0, column "prop". Value "example.com" is not a float number.',
            $rule->validate('example.com'),
        );

        $rule = new Max('prop', 10.1);
        isSame(null, $rule->validate('9'));
        isSame(null, $rule->validate('10.0'));
        isSame(null, $rule->validate('10.1'));
        isSame(
            'Error "max" at line 0, column "prop". Value "10.2" is greater than "10.1".',
            $rule->validate('10.2'),
        );
    }

    public function testMinDate(): void
    {
        $rule = new MinDate('prop', '2000-01-10');
        isSame(null, $rule->validate('2000-01-10'));
        isSame(
            'Error "min_date" at line 0, column "prop". ' .
            'Value "2000-01-09" is less than the minimum date "2000-01-10T00:00:00.000+00:00".',
            $rule->validate('2000-01-09'),
        );

        $rule = new MinDate('prop', '2000-01-10 00:00:00 +01:00');
        isSame(null, $rule->validate('2000-01-10 00:00:00 +01:00'));
        isSame(
            'Error "min_date" at line 0, column "prop". ' .
            'Value "2000-01-09 23:59:59 Europe/Berlin" is less than the minimum date "2000-01-10T00:00:00.000+01:00".',
            $rule->validate('2000-01-09 23:59:59 Europe/Berlin'),
        );

        $rule = new MinDate('prop', '-1000 years');
        isSame(null, $rule->validate('2000-01-10 00:00:00 +01:00'));
    }

    public function testMaxDate(): void
    {
        $rule = new MaxDate('prop', '2000-01-10');
        isSame(null, $rule->validate('2000-01-09'));
        isSame(
            'Error "max_date" at line 0, column "prop". ' .
            'Value "2000-01-11" is more than the maximum date "2000-01-10T00:00:00.000+00:00".',
            $rule->validate('2000-01-11'),
        );

        $rule = new MaxDate('prop', '2000-01-10 00:00:00');
        isSame(null, $rule->validate('2000-01-10 00:00:00'));
        isSame(
            'Error "max_date" at line 0, column "prop". ' .
            'Value "2000-01-10 00:00:01" is more than the maximum date "2000-01-10T00:00:00.000+00:00".',
            $rule->validate('2000-01-10 00:00:01'),
        );

        $rule = new MaxDate('prop', '+1 day');
        isSame(null, $rule->validate('2000-01-10 00:00:00 +01:00'));
    }

    public function testMinLength(): void
    {
        $rule = new MinLength('prop', 5);
        isSame(null, $rule->validate('12345'));
        isSame(null, $rule->validate('     '));
        isSame(null, $rule->validate('  1  '));
        isSame(
            'Error "min_length" at line 0, column "prop". Value "1234" (legth: 4) is too short. Min length is 5.',
            $rule->validate('1234'),
        );
        isSame(
            'Error "min_length" at line 0, column "prop". Value "123 " (legth: 4) is too short. Min length is 5.',
            $rule->validate('123 '),
        );
        isSame(
            'Error "min_length" at line 0, column "prop". Value "" (legth: 0) is too short. Min length is 5.',
            $rule->validate(''),
        );
    }

    public function testMaxLength(): void
    {
        $rule = new MaxLength('prop', 5);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('1234'));
        isSame(null, $rule->validate('12345'));
        isSame(null, $rule->validate('     '));
        isSame(null, $rule->validate('  1  '));
        isSame(
            'Error "max_length" at line 0, column "prop". Value "123456" (legth: 6) is too long. Max length is 5.',
            $rule->validate('123456'),
        );
        isSame(
            'Error "max_length" at line 0, column "prop". Value "12345 " (legth: 6) is too long. Max length is 5.',
            $rule->validate('12345 '),
        );
    }

    public function testNotEmpty(): void
    {
        $rule = new NotEmpty('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate(' 0'));
        isSame(null, $rule->validate(' '));
        isSame(
            'Error "not_empty" at line 0, column "prop". Value is empty.',
            $rule->validate(''),
        );
        isSame(
            'Error "not_empty" at line 0, column "prop". Value is empty.',
            $rule->validate(null),
        );
    }

    public function testOnlyCapitalize(): void
    {
        $rule = new OnlyCapitalize('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('False'));
        isSame(null, $rule->validate('Qwe Rty'));
        isSame(null, $rule->validate(' Qwe Rty'));
        isSame(null, $rule->validate(' '));
        isSame(
            'Error "only_capitalize" at line 0, column "prop". Value "qwerty" should be in capitalize.',
            $rule->validate('qwerty'),
        );
        isSame(
            'Error "only_capitalize" at line 0, column "prop". Value "qwe Rty" should be in capitalize.',
            $rule->validate('qwe Rty'),
        );
    }

    public function testOnlyLowercase(): void
    {
        $rule = new OnlyLowercase('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('qwe rty'));
        isSame(null, $rule->validate(' qwe rty'));
        isSame(null, $rule->validate(' '));
        isSame(
            'Error "only_lowercase" at line 0, column "prop". Value "Qwerty" should be in lowercase.',
            $rule->validate('Qwerty'),
        );
        isSame(
            'Error "only_lowercase" at line 0, column "prop". Value "qwe Rty" should be in lowercase.',
            $rule->validate('qwe Rty'),
        );
    }

    public function testOnlyUppercase(): void
    {
        $rule = new OnlyUppercase('prop');
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('FALSE'));
        isSame(null, $rule->validate('QWE RTY'));
        isSame(null, $rule->validate(' '));
        isSame(
            'Error "only_uppercase" at line 0, column "prop". Value "Qwerty" is not uppercase.',
            $rule->validate('Qwerty'),
        );
        isSame(
            'Error "only_uppercase" at line 0, column "prop". Value "qwe Rty" is not uppercase.',
            $rule->validate('qwe Rty'),
        );
    }

    public function testPrecision(): void
    {
        $rule = new Precision('prop', 0);
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('10'));
        isSame(null, $rule->validate('-10'));
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "1.1" has a precision of 1 but should have a precision of 0.',
            $rule->validate('1.1'),
        );
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "1.0" has a precision of 1 but should have a precision of 0.',
            $rule->validate('1.0'),
        );

        $rule = new Precision('prop', 1);
        isSame(null, $rule->validate('0.0'));
        isSame(null, $rule->validate('10.0'));
        isSame(null, $rule->validate('-10.0'));
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "1" has a precision of 0 but should have a precision of 1.',
            $rule->validate('1'),
        );
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "1.01" has a precision of 2 but should have a precision of 1.',
            $rule->validate('1.01'),
        );

        $rule = new Precision('prop', 2);
        isSame(null, $rule->validate('0.01'));
        isSame(null, $rule->validate('10.00'));
        isSame(null, $rule->validate('-10.00'));
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a precision of 2.',
            $rule->validate('2.0'),
        );
        isSame(
            'Error "precision" at line 0, column "prop". ' .
            'Value "1.000" has a precision of 3 but should have a precision of 2.',
            $rule->validate('1.000'),
        );
    }

    public function testRegex(): void
    {
        $rule = new Regex('prop', '/^a/');
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('aaa'));
        isSame(null, $rule->validate('a'));
        isSame(
            'Error "regex" at line 0, column "prop". Value "1bc" does not match the pattern "/^a/".',
            $rule->validate('1bc'),
        );

        $rule = new Regex('prop', '^a');
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('aaa'));
        isSame(null, $rule->validate('a'));
        isSame(
            'Error "regex" at line 0, column "prop". Value "1bc" does not match the pattern "/^a/u".',
            $rule->validate('1bc'),
        );
    }
}
