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

namespace JBZoo\PHPUnit;

use JBZoo\CsvBlueprint\Csv\CsvFile;

final class CsvReaderTest extends PHPUnit
{
    private const CSV_SIMPLE_HEADER    = __DIR__ . '/fixtures/simple_header.csv';
    private const CSV_SIMPLE_NO_HEADER = __DIR__ . '/fixtures/simple_no_header.csv';

    private const SCHEMA_SIMPLE_HEADER    = __DIR__ . '/schemas/simple_header.yml';
    private const SCHEMA_SIMPLE_NO_HEADER = __DIR__ . '/schemas/simple_no_header.yml';

    public function testReadCsvFileWithoutHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_NO_HEADER, self::SCHEMA_SIMPLE_NO_HEADER);
        isSame(self::CSV_SIMPLE_NO_HEADER, $csv->getCsvFilename());

        isSame([], $csv->getHeader());

        isSame([
            ['1', 'true'],
            ['2', 'true'],
            ['3', 'false'],
        ], $this->fetchRows($csv->getRecords()));

        isSame([['2', 'true']], $this->fetchRows($csv->getRecordsChunk(1, 1)));

        isSame([['2', 'true'], ['3', 'false']], $this->fetchRows($csv->getRecordsChunk(1)));
    }

    public function testReadCsvFileWithHeader(): void
    {
        $csv = new CsvFile(self::CSV_SIMPLE_HEADER, self::SCHEMA_SIMPLE_HEADER);
        isSame(self::CSV_SIMPLE_HEADER, $csv->getCsvFilename());

        isSame(['seq', 'bool', 'exact'], $csv->getHeader());

        isSame([
            ['seq' => '1', 'bool' => 'true', 'exact' => '1'],
            ['seq' => '2', 'bool' => 'true', 'exact' => '1'],
            ['seq' => '3', 'bool' => 'false', 'exact' => '1'],
        ], $this->fetchRows($csv->getRecords()));

        isSame(
            [['seq' => '2', 'bool' => 'true', 'exact' => '1']],
            $this->fetchRows($csv->getRecordsChunk(1, 1)),
        );

        isSame(
            [['seq' => '2', 'bool' => 'true', 'exact' => '1'], ['seq' => '3', 'bool' => 'false', 'exact' => '1']],
            $this->fetchRows($csv->getRecordsChunk(1, 2)),
        );
    }

    private function fetchRows(iterable $records): array
    {
        return \array_reduce(\iterator_to_array($records), static function ($acc, $record) {
            $acc[] = $record;

            return $acc;
        }, []);
    }
}
