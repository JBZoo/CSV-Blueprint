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

namespace JBZoo\PHPUnit\Benchmarks\Commands;

use Faker\Factory;
use JBZoo\Cli\CliCommand;
use JBZoo\CsvBlueprint\Utils;
use League\Csv\Writer;
use Symfony\Component\Console\Input\InputOption;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class CreateCsv extends CliCommand
{
    private const COLUMN_NAME_MAP = [
        1  => 'tiny',
        3  => 'small',
        5  => 'medium',
        10 => 'large',
        20 => 'huge',
    ];

    private const ROW_NAME_MAP = [
        1_000     => '1K',
        1_00_000  => '100K',
        1_000_000 => '1M',
    ];

    protected function configure(): void
    {
        $this
            ->setName('create:csv')
            ->setDescription('Create CSV file with random data based on PHP Faker')
            ->addOption('rows', 'R', InputOption::VALUE_REQUIRED, 'Number of rows', 1_000)
            ->addOption('add-header', 'H', InputOption::VALUE_NONE, 'Add header row')
            ->addOption('columns', 'C', InputOption::VALUE_REQUIRED, 'Columns', 0);

        parent::configure();
    }

    protected function executeAction(): int
    {
        $addHeader = $this->getOptBool('add-header');
        $rows = $this->getOptInt('rows');
        $columns = $this->getOptInt('columns');

        $outputFile = $this->getFilename();
        $writer = Writer::createFromPath($outputFile, 'w+');

        if ($addHeader) {
            $writer->insertOne(\array_keys($this->getDatasetRow($columns)));
            if ($rows === 0) {
                $this->_('Only header created.');
                $this->_('File created: ' . Utils::printFile($outputFile));
                return self::SUCCESS;
            }
        }

        foreach (\range(0, $rows - 1) as $index) {
            $writer->insertOne($this->getDatasetRow($columns, $index + 1));
        }

        $this->_('File created: ' . Utils::printFile($outputFile));

        return self::SUCCESS;
    }

    private function getDatasetRow(int $dataset, int $i = 0): array
    {
        $faker = Factory::create();
        $data = [
            'id'              => static fn () => $i,                                            // 1
            'bool_int'        => static fn () => \random_int(0, 1),                             // 2
            'bool_str'        => static fn () => \random_int(0, 1) === 1 ? 'true' : 'false',    // 3
            'number'          => static fn () => \random_int(0, 1_000_000),                     // 4
            'float'           => static fn () => \random_int(0, 10_000_000) / 7,                // 5
            'date'            => static fn () => $faker->date(),                                // 6
            'datetime'        => static fn () => $faker->date('Y-m-d H:i:s'),                   // 7
            'domain'          => static fn () => $faker->domainName(),                          // 8
            'email'           => static fn () => $faker->email(),                               // 9
            'ip4'             => static fn () => $faker->ipv4(),                                // 10
            'ip6'             => static fn () => $faker->ipv6(),                                // 11
            'uuid'            => static fn () => $faker->uuid(),                                // 12
            'address'         => static fn () => \str_replace("\n", '; ', $faker->address()),    // 13
            'postcode'        => static fn () => $faker->postcode(),                            // 14
            'latitude'        => static fn () => $faker->latitude(),                            // 15
            'longitude'       => static fn () => $faker->longitude(),                           // 16
            'sentence_tiny'   => static fn () => $faker->sentence(3),                           // 17
            'sentence_small'  => static fn () => $faker->sentence(6),                           // 18
            'sentence_medium' => static fn () => $faker->sentence(10),                          // 19
            'sentence_huge'   => static fn () => $faker->sentence(30),                          // 20
        ];

        $firstN = $data;
        if ($dataset > 0) {
            $firstN = \array_slice($data, 0, $dataset, true);
        }

        return \array_map(static fn ($fn) => $fn(), $firstN);
    }

    private function getFilename(): string
    {
        $addHeader = $this->getOptBool('add-header');
        $rows = $this->getOptInt('rows');
        $columns = $this->getOptInt('columns');

        if ($rows === 0) {
            return $addHeader
                ? PATH_ROOT . "/build/bench/{$columns}_header.csv"
                : PATH_ROOT . "/build/bench/{$columns}.csv";
        }

        return $addHeader
            ? PATH_ROOT . "/build/bench/{$columns}_{$rows}_header.csv"
            : PATH_ROOT . "/build/bench/{$columns}_{$rows}.csv";
    }
}
