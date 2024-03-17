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

namespace JBZoo\PHPUnit\Csv;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

final class CsvFileTest extends TestCase
{
    public function testReadCsvFileWithoutHeader(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_NO_HEADER, Tools::SCHEMA_SIMPLE_NO_HEADER);
        isSame(Tools::CSV_SIMPLE_NO_HEADER, $csv->getCsvFilename());

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
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER);
        isSame(Tools::CSV_SIMPLE_HEADER, $csv->getCsvFilename());

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
