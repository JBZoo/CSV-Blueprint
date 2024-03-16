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

use JBZoo\PHPUnit\PHPUnit;
use JBZoo\Utils\Str;

use function JBZoo\PHPUnit\isSame;

final class CellRulesTest extends PHPUnit
{
    protected function setUp(): void
    {
        \date_default_timezone_set('UTC');
    }

    public function testDateFormat(): void
    {
        $rule = $this->create('Y-m-d');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('2000-12-31'));
        isSame(
            '"date_format" at line 1, column "prop". ' .
            'Date format of value "12" is not valid. Expected format: "Y-m-d".',
            $rule->test('12'),
        );
        isSame(
            '"date_format" at line 1, column "prop". ' .
            'Date format of value "2000-01-02 12:34:56" is not valid. Expected format: "Y-m-d".',
            $rule->test('2000-01-02 12:34:56'),
        );
    }

    public function testExactValue(): void
    {
        $rule = $this->create('123');
        isSame('', $rule->validate('123'));
        isSame(
            '"exact_value" at line 1, column "prop". Value "" is not strict equal to "123".',
            $rule->test(''),
        );
        isSame(
            '"exact_value" at line 1, column "prop". Value "2000-01-02" is not strict equal to "123".',
            $rule->test('2000-01-02'),
        );
    }

    public function testIsBool(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('true'));
        isSame('', $rule->validate('false'));
        isSame('', $rule->validate('TRUE'));
        isSame('', $rule->validate('FALSE'));
        isSame('', $rule->validate('True'));
        isSame('', $rule->validate('False'));
        isSame(
            '"is_bool" at line 1, column "prop". Value "1" is not allowed. Allowed values: ["true", "false"].',
            $rule->test('1'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('1'));
    }

    public function testIsDomain(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('example.com'));
        isSame('', $rule->validate('sub.example.com'));
        isSame('', $rule->validate('sub.sub.example.com'));
        isSame('', $rule->validate('sub.sub-example.com'));
        isSame('', $rule->validate('sub-sub-example.com'));
        isSame('', $rule->validate('sub-sub-example.qwerty'));
        isSame(
            '"is_domain" at line 1, column "prop". Value "example" is not a valid domain.',
            $rule->test('example'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('example'));
    }

    public function testIsEmail(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate('user@example.com'));
        isSame('', $rule->validate('user@sub.example.com'));
        isSame(
            '"is_email" at line 1, column "prop". Value "user:pass@example.com" is not a valid email.',
            $rule->test('user:pass@example.com'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('user:pass@example.com'));
    }

    public function testIsFloat(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('1'));
        isSame('', $rule->validate('01'));
        isSame('', $rule->validate('1.0'));
        isSame('', $rule->validate('01.0'));
        isSame('', $rule->validate('-1'));
        isSame('', $rule->validate('-1.0'));
        isSame(
            '"is_float" at line 1, column "prop". Value "1.000.000" is not a float number.',
            $rule->test('1.000.000'),
        );
        isSame(
            '"is_float" at line 1, column "prop". Value " 1" is not a float number.',
            $rule->test(' 1'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate(' 1'));
    }

    public function testIsInt(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('1'));
        isSame('', $rule->validate('01'));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('00'));
        isSame('', $rule->validate('-1'));
        isSame(
            '"is_int" at line 1, column "prop". Value "1.000.000" is not an integer.',
            $rule->test('1.000.000'),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1.1" is not an integer.',
            $rule->test('1.1'),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1.0" is not an integer.',
            $rule->test('1.0'),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value " 1" is not an integer.',
            $rule->test(' 1'),
        );
        isSame(
            '"is_int" at line 1, column "prop". Value "1 " is not an integer.',
            $rule->test('1 '),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate(' 1'));
    }

    public function testIsIp(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('127.0.0.1'));
        isSame('', $rule->validate('0.0.0.0'));
        isSame(
            '"is_ip" at line 1, column "prop". Value "1.2.3" is not a valid IP.',
            $rule->test('1.2.3'),
        );
    }

    public function testIsLatitude(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('90'));
        isSame('', $rule->validate('-90'));
        isSame(
            '"is_latitude" at line 1, column "prop". Value "123" is not a valid latitude (-90 -> 90).',
            $rule->test('123'),
        );
        isSame(
            '"is_latitude" at line 1, column "prop". Value "90.1" is not a valid latitude (-90 -> 90).',
            $rule->test('90.1'),
        );
        isSame(
            '"is_latitude" at line 1, column "prop". Value "90.1.1.1.1" is not a float number.',
            $rule->test('90.1.1.1.1'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('90.1.1.1.1'));
    }

    public function testIsLongitude(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('180'));
        isSame('', $rule->validate('-180'));
        isSame(
            '"is_longitude" at line 1, column "prop". Value "1230" is not a valid longitude (-180 -> 180).',
            $rule->test('1230'),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". ' .
            'Value "180.0001" is not a valid longitude (-180 -> 180).',
            $rule->test('180.0001'),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". Value "-180.1" is not a valid longitude (-180 -> 180).',
            $rule->test('-180.1'),
        );
        isSame(
            '"is_longitude" at line 1, column "prop". Value "1.0.0.0" is not a float number.',
            $rule->test('1.0.0.0'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('1.0.0.0'));
    }

    public function testIsUrl(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('http://example.com'));
        isSame('', $rule->validate('http://example.com/home-page'));
        isSame('', $rule->validate('ftp://user:pass@example.com/home-page?param=value&v=asd#anchor'));
        isSame(
            '"is_url" at line 1, column "prop". Value "123" is not a valid URL.',
            $rule->test('123'),
        );
        isSame(
            '"is_url" at line 1, column "prop". Value "//example.com" is not a valid URL.',
            $rule->test('//example.com'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('//example.com'));
    }

    public function testNotEmpty(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('false'));
        isSame('', $rule->validate('1'));
        isSame('', $rule->validate(' 0'));
        isSame('', $rule->validate(' '));
        isSame(
            '"not_empty" at line 1, column "prop". Value is empty.',
            $rule->test(''),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate(''));
    }

    public function testIsCapitalize(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('False'));
        isSame('', $rule->validate('Qwe Rty'));
        isSame('', $rule->validate(' Qwe Rty'));
        isSame('', $rule->validate(' '));
        isSame(
            '"is_capitalize" at line 1, column "prop". Value "qwerty" should be in capitalize.',
            $rule->test('qwerty'),
        );
        isSame(
            '"is_capitalize" at line 1, column "prop". Value "qwe Rty" should be in capitalize.',
            $rule->test('qwe Rty'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('qwerty'));
    }

    public function testIsLowercase(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('false'));
        isSame('', $rule->validate('qwe rty'));
        isSame('', $rule->validate(' qwe rty'));
        isSame('', $rule->validate(' '));
        isSame(
            '"is_lowercase" at line 1, column "prop". Value "Qwerty" should be in lowercase.',
            $rule->test('Qwerty'),
        );
        isSame(
            '"is_lowercase" at line 1, column "prop". Value "qwe Rty" should be in lowercase.',
            $rule->test('qwe Rty'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('Qwerty'));
    }

    public function testIsUppercase(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('FALSE'));
        isSame('', $rule->validate('QWE RTY'));
        isSame('', $rule->validate(' '));
        isSame(
            '"is_uppercase" at line 1, column "prop". Value "Qwerty" is not uppercase.',
            $rule->test('Qwerty'),
        );
        isSame(
            '"is_uppercase" at line 1, column "prop". Value "qwe Rty" is not uppercase.',
            $rule->test('qwe Rty'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('Qwerty'));
    }

    public function testPrecision(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('10'));
        isSame('', $rule->validate('-10'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.1" has a precision of 1 but should have a precision of 0.',
            $rule->test('1.1'),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.0" has a precision of 1 but should have a precision of 0.',
            $rule->test('1.0'),
        );

        $rule = $this->create(1);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0.0'));
        isSame('', $rule->validate('10.0'));
        isSame('', $rule->validate('-10.0'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1" has a precision of 0 but should have a precision of 1.',
            $rule->test('1'),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.01" has a precision of 2 but should have a precision of 1.',
            $rule->test('1.01'),
        );

        $rule = $this->create(2);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0.01'));
        isSame('', $rule->validate('10.00'));
        isSame('', $rule->validate('-10.00'));
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a precision of 2.',
            $rule->test('2.0'),
        );
        isSame(
            '"precision" at line 1, column "prop". ' .
            'Value "1.000" has a precision of 3 but should have a precision of 2.',
            $rule->test('1.000'),
        );
    }

    public function testPrecisionMin(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('0.0'));
        isSame('', $rule->validate('0.1'));
        isSame('', $rule->validate('-1.0'));
        isSame('', $rule->validate('10.01'));
        isSame('', $rule->validate('-10.0001'));

        $rule = $this->create(1);
        isSame('', $rule->validate('0.0'));
        isSame('', $rule->validate('10.0'));
        isSame('', $rule->validate('-10.0'));

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2" has a precision of 0 but should have a min precision of 1.',
            $rule->test('2'),
        );

        $rule = $this->create(2);
        isSame('', $rule->validate('10.01'));
        isSame('', $rule->validate('-10.0001'));

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2" has a precision of 0 but should have a min precision of 2.',
            $rule->test('2'),
        );

        isSame(
            '"precision_min" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a min precision of 2.',
            $rule->test('2.0'),
        );
    }

    public function testPrecisionMax(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('0'));
        isSame('', $rule->validate('10'));
        isSame('', $rule->validate('-10'));

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "2.0" has a precision of 1 but should have a max precision of 0.',
            $rule->test('2.0'),
        );

        $rule = $this->create(1);
        isSame('', $rule->validate('0.0'));
        isSame('', $rule->validate('10.0'));
        isSame('', $rule->validate('-10.0'));

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "-2.003" has a precision of 3 but should have a max precision of 1.',
            $rule->test('-2.003'),
        );

        isSame(
            '"precision_max" at line 1, column "prop". ' .
            'Value "2.00000" has a precision of 5 but should have a max precision of 1.',
            $rule->test('2.00000'),
        );
    }

    public function testRegex(): void
    {
        $rule = $this->create('/^a/');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('abc'));
        isSame('', $rule->validate('aaa'));
        isSame('', $rule->validate('a'));
        isSame(
            '"regex" at line 1, column "prop". Value "1bc" does not match the pattern "/^a/".',
            $rule->test('1bc'),
        );

        $rule = $this->create('^a');
        isSame('', $rule->validate('abc'));
        isSame('', $rule->validate('aaa'));
        isSame('', $rule->validate('a'));
        isSame(
            '"regex" at line 1, column "prop". Value "1bc" does not match the pattern "/^a/".',
            $rule->test('1bc'),
        );
    }

    public function testIsCardinalDirection(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('N'));
        isSame('', $rule->validate('S'));
        isSame('', $rule->validate('E'));
        isSame('', $rule->validate('W'));
        isSame('', $rule->validate('NE'));
        isSame('', $rule->validate('SE'));
        isSame('', $rule->validate('NW'));
        isSame('', $rule->validate('SW'));
        isSame('', $rule->validate('none'));
        isSame(
            '"is_cardinal_direction" at line 1, column "prop". Value "qwe" is not allowed. ' .
            'Allowed values: ["N", "S", "E", "W", "NE", "SE", "NW", "SW", "none", ""].',
            $rule->test('qwe'),
        );
    }

    public function testIsUsaMarketName(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('New York, NY'));
        isSame('', $rule->validate('City, ST'));
        isSame(
            '"is_usa_market_name" at line 1, column "prop". ' .
            'Invalid market name format for value ", ST". ' .
            'Market name must have format "New York, NY".',
            $rule->test(', ST'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate(', ST'));
    }

    public function testIsUuid4(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate(Str::uuid()));
        isSame(
            '"is_uuid4" at line 1, column "prop". Value is not a valid UUID v4.',
            $rule->test('123'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('123'));
    }

    public function testStartsWith(): void
    {
        $rule = $this->create('a');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('a'));
        isSame('', $rule->validate('abc'));
        isSame('', $rule->validate(''));

        isSame(
            '"starts_with" at line 1, column "prop". Value " a" must start with "a".',
            $rule->test(' a'),
        );

        $rule = $this->create('');
        isSame(
            '"starts_with" at line 1, column "prop". Rule must contain a prefix value in schema file.',
            $rule->test('a '),
        );
    }

    public function testEndsWith(): void
    {
        $rule = $this->create('a');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('a'));
        isSame('', $rule->validate('cba'));
        isSame('', $rule->validate(''));

        isSame(
            '"ends_with" at line 1, column "prop". Value "a " must end with "a".',
            $rule->test('a '),
        );

        $rule = $this->create('');
        isSame(
            '"ends_with" at line 1, column "prop". Rule must contain a suffix value in schema file.',
            $rule->test('a '),
        );
    }

    public function testWordCount(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate(''));
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have exactly 0 words.',
            $rule->test('cba'),
        );

        $rule = $this->create(2);
        isSame('', $rule->validate('asd, asdasd'));
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have exactly 2 words.',
            $rule->test('cba'),
        );
        isSame(
            '"word_count" at line 1, column "prop". ' .
            'Value "cba 123, 123123" has 1 words, but must have exactly 2 words.',
            $rule->test('cba 123, 123123'),
        );

        isSame(
            '"word_count" at line 1, column "prop". Value "a b c" has 3 words, but must have exactly 2 words.',
            $rule->test('a b c'),
        );
    }

    public function testWordCountMin(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('cba'));

        $rule = $this->create(2);
        isSame('', $rule->validate('asd, asdasd'));
        isSame('', $rule->validate('asd, asdasd asd'));
        isSame('', $rule->validate('asd, asdasd 1232 asdas'));
        isSame(
            '"word_count_min" at line 1, column "prop". ' .
            'Value "cba" has 1 words, but must have at least 2 words.',
            $rule->test('cba'),
        );
        isSame(
            '"word_count_min" at line 1, column "prop". ' .
            'Value "cba 123, 123123" has 1 words, but must have at least 2 words.',
            $rule->test('cba 123, 123123'),
        );
    }

    public function testWordCountMax(): void
    {
        $rule = $this->create(0);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate(''));

        $rule = $this->create(2);
        isSame('', $rule->validate('asd, asdasd'));
        isSame('', $rule->validate('asd, 1232'));
        isSame('', $rule->validate('asd, 1232 113234324 342 . ..'));
        isSame(
            '"word_count_max" at line 1, column "prop". ' .
            'Value "asd, asdasd asd 1232 asdas" has 4 words, but must have no more than 2 words.',
            $rule->test('asd, asdasd asd 1232 asdas'),
        );
    }

    public function testIsAlias(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('123'));

        $rule = $this->create(true);
        isSame(
            '"is_alias" at line 1, column "prop". ' .
            'Value "Qwerty, asd 123" is not a valid alias. Expected "qwerty-asd-123".',
            $rule->test('Qwerty, asd 123'),
        );

        $rule = $this->create(false);
        isSame('', $rule->validate('Qwerty, asd 123'));
    }

    public function testContains(): void
    {
        $rule = $this->create('a');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('a'));
        isSame('', $rule->validate('abc'));
        isSame('', $rule->validate('cba'));
        isSame('', $rule->validate(''));

        isSame(
            '"contains" at line 1, column "prop". Value "Qwerty" must contain "a".',
            $rule->test('Qwerty'),
        );

        $rule = $this->create('');
        isSame(
            '"contains" at line 1, column "prop". Rule must contain at least one char in schema file.',
            $rule->test('Qwerty'),
        );
    }

    public function testDate(): void
    {
        $rule = $this->create('2000-10-02');
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('2000-10-02'));
        isSame('', $rule->validate('2000-10-02 00:00:00'));

        isSame(
            '"date" at line 1, column "prop". ' .
            'Value "2000-10-02 00:00:01" is not equal to the expected date "2000-10-02T00:00:00.000+00:00".',
            $rule->test('2000-10-02 00:00:01'),
        );
    }

    public function testIsGeohash(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->validate(''));
        isSame('', $rule->validate('u4pruydqqvj'));
        isSame('', $rule->validate('u4pruydqqv'));
        isSame('', $rule->validate('u4pruydqq'));
        isSame('', $rule->validate('u4pruydq'));
        isSame('', $rule->validate('u4pruyd'));
        isSame('', $rule->validate('u4pruy'));
        isSame('', $rule->validate('u4pru'));
        isSame('', $rule->validate('u4pr'));
        isSame('', $rule->validate('u4p'));
        isSame('', $rule->validate('u4'));
        isSame('', $rule->validate('u'));

        isSame(
            '"is_geohash" at line 1, column "prop". Value "Qwsad342323423erty" is not a valid Geohash.',
            $rule->test('Qwsad342323423erty'),
        );
    }
}
