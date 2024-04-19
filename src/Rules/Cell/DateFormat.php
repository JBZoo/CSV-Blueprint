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

namespace JBZoo\CsvBlueprint\Rules\Cell;

final class DateFormat extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [self::DEFAULT => ['Y-m-d', 'Check strict format of the date.']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $expectedDateFormat = $this->getOptionAsString();
        if ($expectedDateFormat === '') {
            return 'Date format is not defined';
        }

        if (!self::testDate($cellValue, $expectedDateFormat)) {
            return "Date format of value \"<c>{$cellValue}</c>\" is not valid. " .
                "Expected format: \"<green>{$expectedDateFormat}</green>\"";
        }

        return null;
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|float|int|string
    {
        $regex = self::getRegexList();

        $countByRegex = [];

        foreach ($columnValues as $value) {
            if (!IsDate::testValue($value)) {
                return false;
            }

            foreach ($regex as $format => $pattern) {
                if (\preg_match($pattern, $value) === 1 && self::testDate($value, $format)) {
                    $countByRegex[$format] = ($countByRegex[$format] ?? 0) + 1;
                }
            }
        }

        $originalCount = \count(\array_filter($columnValues, static fn (string $value) => $value !== ''));
        $validFormats = \array_keys(\array_filter($countByRegex, static fn (int $count) => $count === $originalCount));

        if (\count($validFormats) > 0) {
            return \reset($validFormats);
        }

        return false;
    }

    private static function testDate(string $cellValue, string $expectedDateFormat): bool
    {
        $date = \DateTimeImmutable::createFromFormat($expectedDateFormat, $cellValue);
        return !($date === false || $date->format($expectedDateFormat) !== $cellValue);
    }

    /**
     * @return array<string, non-empty-string>
     */
    private static function getRegexList(): array
    {
        return [
            'Y-m-d H:i:s'           => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            'Y/m/d H:i:s'           => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}$/',
            'd-m-Y H:i:s'           => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/',
            'd/m/Y H:i:s'           => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/',
            'm-d-Y H:i:s'           => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/',
            'm/d/Y H:i:s'           => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/',
            'Y-m-d'                 => '/^\d{4}-\d{2}-\d{2}$/',
            'Y/m/d'                 => '/^\d{4}\/\d{2}\/\d{2}$/',
            'd-m-Y'                 => '/^\d{2}-\d{2}-\d{4}$/',
            'd/m/Y'                 => '/^\d{2}\/\d{2}\/\d{4}$/',
            'm-d-Y'                 => '/^\d{2}-\d{2}-\d{4}$/',
            'm/d/Y'                 => '/^\d{2}\/\d{2}\/\d{4}$/',
            'Ymd'                   => '/^\d{8}$/',
            'His'                   => '/^\d{6}$/',
            'Y-m-d\TH:i:s'          => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/',
            'Ymd\THis'              => '/^\d{8}T\d{6}$/',
            'd.m.Y H:i:s'           => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}$/',
            'd.m.Y'                 => '/^\d{2}\.\d{2}\.\d{4}$/',
            'Y.m.d'                 => '/^\d{4}\.\d{2}\.\d{2}$/',
            'Y-m-d\TH:i:sP'         => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/',
            'd-M-Y'                 => '/^\d{2}-[A-Za-z]{3}-\d{4}$/',
            'd F Y'                 => '/^\d{2} [A-Za-z]+ \d{4}$/',
            'D, d M Y H:i:s'        => '/^[A-Za-z]{3}, \d{2} [A-Za-z]{3} \d{4} \d{2}:\d{2}:\d{2}$/',
            'D, d M Y H:i:s O'      => '/^[A-Za-z]{3}, \d{2} [A-Za-z]{3} \d{4} \d{2}:\d{2}:\d{2} \+\d{4}$/',
            'Y-m-d\TH:i:s.uP'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}\+\d{2}:\d{2}$/',
            'YmdHi'                 => '/^\d{10}$/',
            'YmdHis'                => '/^\d{14}$/',
            'Y-m-d H:i'             => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/',
            'Y/m/d H:i'             => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}$/',
            'd-m-Y H:i'             => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}$/',
            'd/m/Y H:i'             => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/',
            'm-d-Y H:i'             => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}$/',
            'm/d/Y H:i'             => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/',
            'd.m.Y H:i'             => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}$/',
            'd-M-Y H:i:s'           => '/^\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2}$/',
            'Ymd\THis\Z'            => '/^\d{8}T\d{6}Z$/',
            'Y-m-d\TH:i:s\Z'        => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            'D M j Y'               => '/^[A-Za-z]{3} [A-Za-z]{3} \d{1,2} \d{4}$/',
            'l, F d, Y'             => '/^[A-Za-z]+, [A-Za-z]+ \d{2}, \d{4}$/',
            'D M d, Y H:i:s T'      => '/^[A-Za-z]{3} [A-Za-z]{3} \d{2}, \d{4} \d{2}:\d{2}:\d{2} [A-Za-z]{3}$/',
            'd-M-Y H:i'             => '/^\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}$/',
            'd F Y H:i:s'           => '/^\d{2} [A-Za-z]+ \d{4} \d{2}:\d{2}:\d{2}$/',
            'Y-m-d H:i:s.u'         => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'YmdHisu'               => '/^\d{14}\d{3}$/',
            'Y-m-d\TH:i:s.uO'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}\+\d{4}$/',
            'Ymd\THisu'             => '/^\d{8}T\d{6}\d{3}$/',
            'Y-m-d H:i:sP'          => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:sO'          => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{4}$/',
            'Y/m/d\TH:i:s'          => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}$/',
            'Y-m-d\TH:i:s.uZ'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/',
            'Y-m-d\TH:i:s.u'        => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd-m-Y H:i:s.u'         => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'd/m/Y H:i:s.u'         => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'd.m.Y H:i:s.u'         => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'Y-m-d H:i:s.uP'        => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}\+\d{2}:\d{2}$/',
            'Y.m.d H:i:s.u'         => '/^\d{4}\.\d{2}\.\d{2} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'D, d M Y H:i:s.u'      => '/^[A-Za-z]{3}, \d{2} [A-Za-z]{3} \d{4} \d{2}:\d{2}:\d{2}\.\d{3}$/',
            'Y-m-d H:i:s.uO'        => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}\+\d{4}$/',
            'Y-m-d H:i:s.u Z'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3} Z$/',
            'Y-m-d H:i:s.u O'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3} \+\d{4}$/',
            'Y-m-d\TH:i:s.v'        => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'Ymd\THisv'             => '/^\d{8}T\d{6}\d{6}$/',
            'Y-m-d H:i:s.vP'        => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'Y/m/d\TH:i:s.v'        => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd-m-Y H:i:s.v'         => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd/m/Y H:i:s.v'         => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd.m.Y H:i:s.v'         => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{6}$/',
            'Y-m-d H:i.v'           => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\.\d{6}$/',
            'Y-m-d\TH:i:s.vZ'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'Y-m-d H:i:s.vZ'        => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'Y/m/d H:i:s.v'         => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/',
            'Y/m/d\TH:i:s.vZ'       => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'Y-m-d\TH:i:s.vP'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:s.v\Z'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'Y-m-d\TH:i:s.vO'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{4}$/',
            'Ymd\THisvZ'            => '/^\d{8}T\d{6}\d{6}Z$/',
            'Ymd\THisvP'            => '/^\d{8}T\d{6}\d{6}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:s.vO'        => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{4}$/',
            'd-m-Y H:i:s.vZ'        => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'd/m/Y H:i:s.vZ'        => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'd.m.Y H:i:s.vZ'        => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'd-m-Y H:i:s.vP'        => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'd/m/Y H:i:s.vP'        => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'd.m.Y H:i:s.vP'        => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'Y-m-d\TH:i:v'          => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}\.\d{6}$/',
            'Y/m/d\TH:i:s.vP'       => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:s.v\+\d{4}'  => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}\+\d{4}$/',
            'Y/m/d H:i:s.v\Z'       => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            'Y-m-d\TH:i:s.v\+\d{4}' => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}\+\d{4}$/',
            'd-m-Y\TH:i:s.v'        => '/^\d{2}-\d{2}-\d{4}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd/m/Y\TH:i:s.v'        => '/^\d{2}\/\d{2}\/\d{4}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'd.m.Y\TH:i:s.v'        => '/^\d{2}\.\d{2}\.\d{4}T\d{2}:\d{2}:\d{2}\.\d{6}$/',
            'Y-m-d\TH:i:s.vv'       => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}$/',
            'Ymd\THisvv'            => '/^\d{8}T\d{6}\d{9}$/',
            'Y-m-d H:i:s.vvP'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{9}\+\d{2}:\d{2}$/',
            'Y/m/d\TH:i:s.vv'       => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}$/',
            'd-m-Y H:i:s.vv'        => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{9}$/',
            'd/m/Y H:i:s.vv'        => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{9}$/',
            'd.m.Y H:i:s.vv'        => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{9}$/',
            'Y-m-d H:i.vv'          => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\.\d{9}$/',
            'Y-m-d\TH:i:s.vvZ'      => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}Z$/',
            'Y-m-d\TH:i:s.vvP'      => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}\+\d{2}:\d{2}$/',
            'd-m-Y H:i:s.vvP'       => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{9}\+\d{2}:\d{2}$/',
            'd/m/Y H:i:s.vvP'       => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{9}\+\d{2}:\d{2}$/',
            'YmdHisvvP'             => '/^\d{14}\d{9}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:s.vvZ'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{9}Z$/',
            'Y-m-d H:i:s.vvO'       => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{9} \+\d{4}$/',
            'Y/m/d H:i:s.vv'        => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}\.\d{9}$/',
            'Y/m/d\TH:i:s.vvZ'      => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{9}Z$/',
            'Y-m-d\TH:i:s.vvv'      => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{12}$/',
            'Ymd\THisvvv'           => '/^\d{8}T\d{6}\d{12}$/',
            'Y-m-d H:i:s.vvvP'      => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{12}\+\d{2}:\d{2}$/',
            'Y/m/d\TH:i:s.vvv'      => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{12}$/',
            'd-m-Y H:i:s.vvv'       => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{12}$/',
            'd/m/Y H:i:s.vvv'       => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{12}$/',
            'd.m.Y H:i:s.vvv'       => '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2}\.\d{12}$/',
            'Y-m-d H:i.vvv'         => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}\.\d{12}$/',
            'Y-m-d\TH:i:s.vvvZ'     => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{12}Z$/',
            'Y-m-d\TH:i:s.vvvP'     => '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{12}\+\d{2}:\d{2}$/',
            'd-m-Y H:i:s.vvvP'      => '/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}\.\d{12}\+\d{2}:\d{2}$/',
            'd/m/Y H:i:s.vvvP'      => '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}\.\d{12}\+\d{2}:\d{2}$/',
            'YmdHisvvvP'            => '/^\d{14}\d{12}\+\d{2}:\d{2}$/',
            'Y-m-d H:i:s.vvvZ'      => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{12}Z$/',
            'Y-m-d H:i:s.vvvO'      => '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{12} \+\d{4}$/',
            'Y/m/d H:i:s.vvv'       => '/^\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2}\.\d{12}$/',
            'Y/m/d\TH:i:s.vvvZ'     => '/^\d{4}\/\d{2}\/\d{2}T\d{2}:\d{2}:\d{2}\.\d{12}Z$/',
        ];
    }
}
