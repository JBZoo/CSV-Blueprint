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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\CsvBlueprint\Rules\Cell\Contains;
use JBZoo\CsvBlueprint\Rules\Cell\ContainsAll;
use JBZoo\CsvBlueprint\Rules\Cell\ContainsOne;
use JBZoo\CsvBlueprint\Rules\Cell\Date;
use JBZoo\CsvBlueprint\Rules\Cell\DateFormat;
use JBZoo\CsvBlueprint\Rules\Cell\EndsWith;
use JBZoo\CsvBlueprint\Rules\Cell\ExactValue;
use JBZoo\CsvBlueprint\Rules\Cell\IsAlias;
use JBZoo\CsvBlueprint\Rules\Cell\IsBool;
use JBZoo\CsvBlueprint\Rules\Cell\IsCapitalize;
use JBZoo\CsvBlueprint\Rules\Cell\IsCardinalDirection;
use JBZoo\CsvBlueprint\Rules\Cell\IsDomain;
use JBZoo\CsvBlueprint\Rules\Cell\IsEmail;
use JBZoo\CsvBlueprint\Rules\Cell\IsFloat;
use JBZoo\CsvBlueprint\Rules\Cell\IsGeohash;
use JBZoo\CsvBlueprint\Rules\Cell\IsInt;
use JBZoo\CsvBlueprint\Rules\Cell\IsIp;
use JBZoo\CsvBlueprint\Rules\Cell\IsLatitude;
use JBZoo\CsvBlueprint\Rules\Cell\IsLongitude;
use JBZoo\CsvBlueprint\Rules\Cell\IsLowercase;
use JBZoo\CsvBlueprint\Rules\Cell\IsUppercase;
use JBZoo\CsvBlueprint\Rules\Cell\IsUrl;
use JBZoo\CsvBlueprint\Rules\Cell\IsUsaMarketName;
use JBZoo\CsvBlueprint\Rules\Cell\IsUuid4;
use JBZoo\CsvBlueprint\Rules\Cell\NotEmpty;
use JBZoo\CsvBlueprint\Rules\Cell\Precision;
use JBZoo\CsvBlueprint\Rules\Cell\PrecisionMax;
use JBZoo\CsvBlueprint\Rules\Cell\PrecisionMin;
use JBZoo\CsvBlueprint\Rules\Cell\Regex;
use JBZoo\CsvBlueprint\Rules\Cell\StartsWith;
use JBZoo\CsvBlueprint\Rules\Cell\WordCount;
use JBZoo\CsvBlueprint\Rules\Cell\WordCountMax;
use JBZoo\CsvBlueprint\Rules\Cell\WordCountMin;
use JBZoo\PHPUnit\PHPUnit;
use JBZoo\Utils\Str;

use function JBZoo\PHPUnit\isSame;

final class CellRulesTest extends PHPUnit
{
    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testAllowValues(): void
    {
    }

    public function testDateFormat(): void
    {
        $rule = new DateFormat('prop', 'Y-m-d');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('2000-12-31'));
        isSame(
            '"date_format" at line 1, column "prop". ' .
            'Date format of value "12" is not valid. Expected format: "Y-m-d".',
            \strip_tags((string)$rule->validate('12')),
        );
        isSame(
            '"date_format" at line 1, column "prop". ' .
            'Date format of value "2000-01-02 12:34:56" is not valid. Expected format: "Y-m-d".',
            \strip_tags((string)$rule->validate('2000-01-02 12:34:56')),
        );
    }

    public function testExactValue(): void
    {
        $rule = new ExactValue('prop', '123');
        isSame(null, $rule->validate('123'));
        isSame(
            '"exact_value" at line 1, column "prop". Value "" is not strict equal to "123".',
            \strip_tags((string)$rule->validate('')),
        );
        isSame(
            '"exact_value" at line 1, column "prop". Value "2000-01-02" is not strict equal to "123".',
            \strip_tags((string)$rule->validate('2000-01-02')),
        );
    }

    public function testIsBool(): void
    {
        $rule = new IsBool('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('true'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('TRUE'));
        isSame(null, $rule->validate('FALSE'));
        isSame(null, $rule->validate('True'));
        isSame(null, $rule->validate('False'));
        isSame(
            '"is_bool" at line 1, column "prop". Value "1" is not allowed. Allowed values: ["true", "false"].',
            \strip_tags((string)$rule->validate('1')),
        );

        $rule = new IsBool('prop', false);
        isSame(null, $rule->validate('1'));
    }

    public function testIsDomain(): void
    {
        $rule = new IsDomain('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('example.com'));
        isSame(null, $rule->validate('sub.example.com'));
        isSame(null, $rule->validate('sub.sub.example.com'));
        isSame(null, $rule->validate('sub.sub-example.com'));
        isSame(null, $rule->validate('sub-sub-example.com'));
        isSame(null, $rule->validate('sub-sub-example.qwerty'));
        isSame(
            '"is_domain" at line 1, column "prop". Value "example" is not a valid domain.',
            \strip_tags((string)$rule->validate('example')),
        );

        $rule = new IsDomain('prop', false);
        isSame(null, $rule->validate('example'));
    }

    public function testIsEmail(): void
    {
        $rule = new IsEmail('prop', true);
        isSame(null, $rule->validate('user@example.com'));
        isSame(null, $rule->validate('user@sub.example.com'));
        isSame(
            '"is_email" at line 1, column "prop". Value "user:pass@example.com" is not a valid email.',
            \strip_tags((string)$rule->validate('user:pass@example.com')),
        );

        $rule = new IsEmail('prop', false);
        isSame(null, $rule->validate('user:pass@example.com'));
    }

    public function testIsFloat(): void
    {
        $rule = new IsFloat('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('01'));
        isSame(null, $rule->validate('1.0'));
        isSame(null, $rule->validate('01.0'));
        isSame(null, $rule->validate('-1'));
        isSame(null, $rule->validate('-1.0'));
        isSame(
            '"is_float" at line 1, column "prop". Value "1.000.000" is not a float number.',
            \strip_tags((string)$rule->validate('1.000.000')),
        );
        isSame(
            '"is_float" at line 1, column "prop". Value " 1" is not a float number.',
            \strip_tags((string)$rule->validate(' 1')),
        );

        $rule = new IsFloat('prop', false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testIsInt(): void
    {
        $rule = new IsInt('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('01'));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('00'));
        isSame(null, $rule->validate('-1'));
        isSame(
            '"is_int" at line 1, column "prop". Value "1.000.000" is not an integer.',
            \strip_tags((string)$rule->validate('1.000.000')),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1.1" is not an integer.',
            \strip_tags((string)$rule->validate('1.1')),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1.0" is not an integer.',
            \strip_tags((string)$rule->validate('1.0')),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value " 1" is not an integer.',
            \strip_tags((string)$rule->validate(' 1')),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1 " is not an integer.',
            \strip_tags((string)$rule->validate('1 ')),
        );

        $rule = new IsInt('prop', false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testIsIp(): void
    {
        $rule = new IsIp('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('127.0.0.1'));
        isSame(null, $rule->validate('0.0.0.0'));
        isSame(
            '"is_ip" at line 1, column "prop". Value "1.2.3" is not a valid IP.',
            \strip_tags((string)$rule->validate('1.2.3')),
        );
    }

    public function testIsLatitude(): void
    {
        $rule = new IsLatitude('prop', true);
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('90'));
        isSame(null, $rule->validate('-90'));
        isSame(
            '"is_latitude" at line 1, column "prop". Value "123" is not a valid latitude (-90 -> 90).',
            \strip_tags((string)$rule->validate('123')),
        );
        isSame(
            '"is_latitude" at line 1, column "prop". Value "90.1" is not a valid latitude (-90 -> 90).',
            \strip_tags((string)$rule->validate('90.1')),
        );
        isSame(
            '"is_latitude" at line 1, column "prop". Value "90.1.1.1.1" is not a float number.',
            \strip_tags((string)$rule->validate('90.1.1.1.1')),
        );

        $rule = new IsLatitude('prop', false);
        isSame(null, $rule->validate('90.1.1.1.1'));
    }

    public function testIsLongitude(): void
    {
        $rule = new IsLongitude('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('180'));
        isSame(null, $rule->validate('-180'));
        isSame(
            '"is_longitude" at line 1, column "prop". Value "1230" is not a valid longitude (-180 -> 180).',
            \strip_tags((string)$rule->validate('1230')),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". ' .
            'Value "180.0001" is not a valid longitude (-180 -> 180).',
            \strip_tags((string)$rule->validate('180.0001')),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". Value "-180.1" is not a valid longitude (-180 -> 180).',
            \strip_tags((string)$rule->validate('-180.1')),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". Value "1.0.0.0" is not a float number.',
            \strip_tags((string)$rule->validate('1.0.0.0')),
        );

        $rule = new IsLongitude('prop', false);
        isSame(null, $rule->validate('1.0.0.0'));
    }

    public function testIsUrl(): void
    {
        $rule = new IsUrl('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('http://example.com'));
        isSame(null, $rule->validate('http://example.com/home-page'));
        isSame(null, $rule->validate('ftp://user:pass@example.com/home-page?param=value&v=asd#anchor'));
        isSame(
            '"is_url" at line 1, column "prop". Value "123" is not a valid URL.',
            \strip_tags((string)$rule->validate('123')),
        );
        isSame(
            '"is_url" at line 1, column "prop". Value "//example.com" is not a valid URL.',
            \strip_tags((string)$rule->validate('//example.com')),
        );

        $rule = new IsUrl('prop', false);
        isSame(null, $rule->validate('//example.com'));
    }

    public function testNotEmpty(): void
    {
        $rule = new NotEmpty('prop', true);
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate(' 0'));
        isSame(null, $rule->validate(' '));
        isSame(
            '"not_empty" at line 1, column "prop". Value is empty.',
            \strip_tags((string)$rule->validate('')),
        );

        $rule = new NotEmpty('prop', false);
        isSame(null, $rule->validate(''));
    }

    public function testIsCapitalize(): void
    {
        $rule = new IsCapitalize('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('False'));
        isSame(null, $rule->validate('Qwe Rty'));
        isSame(null, $rule->validate(' Qwe Rty'));
        isSame(null, $rule->validate(' '));
        isSame(
            '"is_capitalize" at line 1, column "prop". Value "qwerty" should be in capitalize.',
            \strip_tags((string)$rule->validate('qwerty')),
        );
        isSame(
            '"is_capitalize" at line 1, column "prop". Value "qwe Rty" should be in capitalize.',
            \strip_tags((string)$rule->validate('qwe Rty')),
        );

        $rule = new IsCapitalize('prop', false);
        isSame(null, $rule->validate('qwerty'));
    }

    public function testIsLowercase(): void
    {
        $rule = new IsLowercase('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('false'));
        isSame(null, $rule->validate('qwe rty'));
        isSame(null, $rule->validate(' qwe rty'));
        isSame(null, $rule->validate(' '));
        isSame(
            '"is_lowercase" at line 1, column "prop". Value "Qwerty" should be in lowercase.',
            \strip_tags((string)$rule->validate('Qwerty')),
        );
        isSame(
            '"is_lowercase" at line 1, column "prop". Value "qwe Rty" should be in lowercase.',
            \strip_tags((string)$rule->validate('qwe Rty')),
        );

        $rule = new IsLowercase('prop', false);
        isSame(null, $rule->validate('Qwerty'));
    }

    public function testIsUppercase(): void
    {
        $rule = new IsUppercase('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('FALSE'));
        isSame(null, $rule->validate('QWE RTY'));
        isSame(null, $rule->validate(' '));
        isSame(
            '"is_uppercase" at line 1, column "prop". Value "Qwerty" is not uppercase.',
            \strip_tags((string)$rule->validate('Qwerty')),
        );
        isSame(
            '"is_uppercase" at line 1, column "prop". Value "qwe Rty" is not uppercase.',
            \strip_tags((string)$rule->validate('qwe Rty')),
        );

        $rule = new IsUppercase('prop', false);
        isSame(null, $rule->validate('Qwerty'));
    }

    public function testPrecision(): void
    {
        $rule = new Precision('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('10'));
        isSame(null, $rule->validate('-10'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.1" has a precision of 1 but should have a precision of 0.',
            \strip_tags((string)$rule->validate('1.1')),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.0" has a precision of 1 but should have a precision of 0.',
            \strip_tags((string)$rule->validate('1.0')),
        );

        $rule = new Precision('prop', 1);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0.0'));
        isSame(null, $rule->validate('10.0'));
        isSame(null, $rule->validate('-10.0'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1" has a precision of 0 but should have a precision of 1.',
            \strip_tags((string)$rule->validate('1')),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.01" has a precision of 2 but should have a precision of 1.',
            \strip_tags((string)$rule->validate('1.01')),
        );

        $rule = new Precision('prop', 2);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0.01'));
        isSame(null, $rule->validate('10.00'));
        isSame(null, $rule->validate('-10.00'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a precision of 2.',
            \strip_tags((string)$rule->validate('2.0')),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.000" has a precision of 3 but should have a precision of 2.',
            \strip_tags((string)$rule->validate('1.000')),
        );
    }

    public function testPrecisionMin(): void
    {
        $rule = new PrecisionMin('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('0.0'));
        isSame(null, $rule->validate('0.1'));
        isSame(null, $rule->validate('-1.0'));
        isSame(null, $rule->validate('10.01'));
        isSame(null, $rule->validate('-10.0001'));

        $rule = new PrecisionMin('prop', 1);
        isSame(null, $rule->validate('0.0'));
        isSame(null, $rule->validate('10.0'));
        isSame(null, $rule->validate('-10.0'));

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2" has a precision of 0 but should have a min precision of 1.',
            \strip_tags((string)$rule->validate('2')),
        );

        $rule = new PrecisionMin('prop', 2);
        isSame(null, $rule->validate('10.01'));
        isSame(null, $rule->validate('-10.0001'));

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2" has a precision of 0 but should have a min precision of 2.',
            \strip_tags((string)$rule->validate('2')),
        );

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a min precision of 2.',
            \strip_tags((string)$rule->validate('2.0')),
        );
    }

    public function testPrecisionMax(): void
    {
        $rule = new PrecisionMax('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('0'));
        isSame(null, $rule->validate('10'));
        isSame(null, $rule->validate('-10'));

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a max precision of 0.',
            \strip_tags((string)$rule->validate('2.0')),
        );

        $rule = new PrecisionMax('prop', 1);
        isSame(null, $rule->validate('0.0'));
        isSame(null, $rule->validate('10.0'));
        isSame(null, $rule->validate('-10.0'));

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "-2.003" has a precision of 3 but should have a max precision of 1.',
            \strip_tags((string)$rule->validate('-2.003')),
        );

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "2.00000" has a precision of 5 but should have a max precision of 1.',
            \strip_tags((string)$rule->validate('2.00000')),
        );
    }

    public function testRegex(): void
    {
        $rule = new Regex('prop', '/^a/');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('aaa'));
        isSame(null, $rule->validate('a'));
        isSame(
            '"regex" at line 1, column "prop". Value "1bc" does not match the pattern "/^a/".',
            \strip_tags((string)$rule->validate('1bc')),
        );

        $rule = new Regex('prop', '^a');
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('aaa'));
        isSame(null, $rule->validate('a'));
        isSame(
            '"regex" at line 1, column "prop". Value "1bc" does not match the pattern "/^a/".',
            \strip_tags((string)$rule->validate('1bc')),
        );
    }

    public function testIsCardinalDirection(): void
    {
        $rule = new IsCardinalDirection('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('N'));
        isSame(null, $rule->validate('S'));
        isSame(null, $rule->validate('E'));
        isSame(null, $rule->validate('W'));
        isSame(null, $rule->validate('NE'));
        isSame(null, $rule->validate('SE'));
        isSame(null, $rule->validate('NW'));
        isSame(null, $rule->validate('SW'));
        isSame(null, $rule->validate('none'));
        isSame(
            '"is_cardinal_direction" at line 1, column "prop". Value "qwe" is not allowed. ' .
            'Allowed values: ["N", "S", "E", "W", "NE", "SE", "NW", "SW", "none", ""].',
            \strip_tags((string)$rule->validate('qwe')),
        );
    }

    public function testIsUsaMarketName(): void
    {
        $rule = new IsUsaMarketName('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('New York, NY'));
        isSame(null, $rule->validate('City, ST'));
        isSame(
            '"is_usa_market_name" at line 1, column "prop". ' .
            'Invalid market name format for value ", ST". ' .
            'Market name must have format "New York, NY".',
            \strip_tags((string)$rule->validate(', ST')),
        );

        $rule = new IsUsaMarketName('prop', false);
        isSame(null, $rule->validate(', ST'));
    }

    public function testIsUuid4(): void
    {
        $rule = new IsUuid4('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate(Str::uuid()));
        isSame(
            '"is_uuid4" at line 1, column "prop". Value is not a valid UUID v4.',
            \strip_tags((string)$rule->validate('123')),
        );

        $rule = new IsUuid4('prop', false);
        isSame(null, $rule->validate('123'));
    }

    public function testContainsOne(): void
    {
        $rule = new ContainsOne('prop', []);
        isSame(null, $rule->validate(''));
        isSame(
            '"contains_one" at line 1, column "prop". ' .
            'Rule must contain at least one inclusion value in schema file.',
            \strip_tags((string)$rule->validate('123')),
        );

        $rule = new ContainsOne('prop', ['a', 'b', 'c']);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('a'));
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('adasdasdasdc'));

        isSame(
            '"contains_one" at line 1, column "prop". ' .
            'Value "123" must contain at least one of the following: "["a", "b", "c"]".',
            \strip_tags((string)$rule->validate('123')),
        );
    }

    public function testContainsAll(): void
    {
        $rule = new ContainsAll('prop', []);
        isSame(null, $rule->validate(''));
        isSame(
            '"contains_all" at line 1, column "prop". Rule must contain at least one inclusion value in schema file.',
            \strip_tags((string)$rule->validate('ac')),
        );

        $rule = new ContainsAll('prop', ['a', 'b', 'c']);
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('abdasadasdasdc'));

        isSame(
            '"contains_all" at line 1, column "prop". Value "ab" must contain all of the following: "["a", "b", "c"]".',
            \strip_tags((string)$rule->validate('ab')),
        );
        isSame(
            '"contains_all" at line 1, column "prop". Value "ac" must contain all of the following: "["a", "b", "c"]".',
            \strip_tags((string)$rule->validate('ac')),
        );
    }

    public function testStartsWith(): void
    {
        $rule = new StartsWith('prop', 'a');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('a'));
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate(''));

        isSame(
            '"starts_with" at line 1, column "prop". Value " a" must start with "a".',
            \strip_tags((string)$rule->validate(' a')),
        );

        $rule = new StartsWith('prop', '');
        isSame(
            '"starts_with" at line 1, column "prop". Rule must contain a prefix value in schema file.',
            \strip_tags((string)$rule->validate('a ')),
        );
    }

    public function testEndsWith(): void
    {
        $rule = new EndsWith('prop', 'a');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('a'));
        isSame(null, $rule->validate('cba'));
        isSame(null, $rule->validate(''));

        isSame(
            '"ends_with" at line 1, column "prop". Value "a " must end with "a".',
            \strip_tags((string)$rule->validate('a ')),
        );

        $rule = new EndsWith('prop', '');
        isSame(
            '"ends_with" at line 1, column "prop". Rule must contain a suffix value in schema file.',
            \strip_tags((string)$rule->validate('a ')),
        );
    }

    public function testWordCount(): void
    {
        $rule = new WordCount('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate(''));
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have exactly 0 words.',
            \strip_tags((string)$rule->validate('cba')),
        );

        $rule = new WordCount('prop', 2);
        isSame(null, $rule->validate('asd, asdasd'));
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have exactly 2 words.',
            \strip_tags((string)$rule->validate('cba')),
        );
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba 123, 123123" has 1 words, but must have exactly 2 words.',
            \strip_tags((string)$rule->validate('cba 123, 123123')),
        );

        isSame(
            '"word_count" at line 1, column "prop". Value "a b c" has 3 words, but must have exactly 2 words.',
            \strip_tags((string)$rule->validate('a b c')),
        );
    }

    public function testWordCountMin(): void
    {
        $rule = new WordCountMin('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('cba'));

        $rule = new WordCountMin('prop', 2);
        isSame(null, $rule->validate('asd, asdasd'));
        isSame(null, $rule->validate('asd, asdasd asd'));
        isSame(null, $rule->validate('asd, asdasd 1232 asdas'));
        isSame(
            '"word_count_min" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have at least 2 words.',
            \strip_tags((string)$rule->validate('cba')),
        );
        isSame(
            '"word_count_min" at line 1, column "prop". ' .
            'Value "cba 123, 123123" has 1 words, but must have at least 2 words.',
            \strip_tags((string)$rule->validate('cba 123, 123123')),
        );
    }

    public function testWordCountMax(): void
    {
        $rule = new WordCountMax('prop', 0);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate(''));

        $rule = new WordCountMax('prop', 2);
        isSame(null, $rule->validate('asd, asdasd'));
        isSame(null, $rule->validate('asd, 1232'));
        isSame(null, $rule->validate('asd, 1232 113234324 342 . ..'));
        isSame(
            '"word_count_max" at line 1, column "prop". ' .
            'Value "asd, asdasd asd 1232 asdas" has 4 words, but must have no more than 2 words.',
            \strip_tags((string)$rule->validate('asd, asdasd asd 1232 asdas')),
        );
    }

    public function testIsAlias(): void
    {
        $rule = new IsAlias('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('123'));

        $rule = new IsAlias('prop', true);
        isSame(
            '"is_alias" at line 1, column "prop". ' .
            'Value "Qwerty, asd 123" is not a valid alias. Expected "qwerty-asd-123".',
            \strip_tags((string)$rule->validate('Qwerty, asd 123')),
        );

        $rule = new IsAlias('prop', false);
        isSame(null, $rule->validate('Qwerty, asd 123'));
    }

    public function testContains(): void
    {
        $rule = new Contains('prop', 'a');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('a'));
        isSame(null, $rule->validate('abc'));
        isSame(null, $rule->validate('cba'));
        isSame(null, $rule->validate(''));

        isSame(
            '"contains" at line 1, column "prop". Value "Qwerty" must contain "a".',
            \strip_tags((string)$rule->validate('Qwerty')),
        );

        $rule = new Contains('prop', '');
        isSame(
            '"contains" at line 1, column "prop". Rule must contain at least one char in schema file.',
            \strip_tags((string)$rule->validate('Qwerty')),
        );
    }

    public function testDate(): void
    {
        $rule = new Date('prop', '2000-10-02');
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('2000-10-02'));
        isSame(null, $rule->validate('2000-10-02 00:00:00'));

        isSame(
            '"date" at line 1, column "prop". ' .
            'Value "2000-10-02 00:00:01" is not equal to the expected date "2000-10-02T00:00:00.000+00:00".',
            \strip_tags((string)$rule->validate('2000-10-02 00:00:01')),
        );
    }

    public function testIsGeohash(): void
    {
        $rule = new IsGeohash('prop', true);
        isSame(null, $rule->validate(''));
        isSame(null, $rule->validate('u4pruydqqvj'));
        isSame(null, $rule->validate('u4pruydqqv'));
        isSame(null, $rule->validate('u4pruydqq'));
        isSame(null, $rule->validate('u4pruydq'));
        isSame(null, $rule->validate('u4pruyd'));
        isSame(null, $rule->validate('u4pruy'));
        isSame(null, $rule->validate('u4pru'));
        isSame(null, $rule->validate('u4pr'));
        isSame(null, $rule->validate('u4p'));
        isSame(null, $rule->validate('u4'));
        isSame(null, $rule->validate('u'));

        isSame(
            '"is_geohash" at line 1, column "prop". Value "Qwsad342323423erty" is not a valid Geohash.',
            \strip_tags((string)$rule->validate('Qwsad342323423erty')),
        );
    }
}
