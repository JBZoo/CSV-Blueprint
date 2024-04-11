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

namespace JBZoo\PHPUnit\Validators;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;

use function JBZoo\PHPUnit\isSame;

final class CsvValidatorTest extends TestCase
{
    public function testUndefinedRule(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('seq', 'undefined_rule', true));
        isSame('', (string)$csv->validate());
    }

    public function testValidWithHeader(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER);
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testInvalidWithoutHeader(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_NO_HEADER, Tools::SCHEMA_SIMPLE_NO_HEADER);
        isSame(
            <<<'TEXT'
                "allow_extra_columns" at line 1. Schema number of columns "3" greater than real "2".
                
                TEXT,
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testInvalidSchemaFile(): void
    {
        $this->expectExceptionMessage('Invalid schema data: undefined_file_name.yml');
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, 'undefined_file_name.yml');
    }

    public function testSchemaAsPhpFile(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER_PHP);
        isSame(
            '"num_min" at line 2, column "0:seq". The value "1" is less than the expected "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testSchemaAsJsonFile(): void
    {
        $csv = new CsvFile(Tools::CSV_SIMPLE_HEADER, Tools::SCHEMA_SIMPLE_HEADER_JSON);
        isSame(
            '"num_min" at line 2, column "0:seq". The value "1" is less than the expected "2".' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testCellRule(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('seq', 'not_empty', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('integer', 'not_empty', true));
        isSame(
            '"not_empty" at line 19, column "3:integer". Value is empty.' . "\n",
            \strip_tags((string)$csv->validate()),
        );
    }

    public function testAggregateRule(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Name', 'is_unique', true));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('City', 'is_unique', true));
        isSame(
            '"ag:is_unique" at line 1, column "1:City". Column has non-unique values. Unique: 9, total: 10.' . "\n",
            \strip_tags((string)$csv->validate()),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('City', 'is_unique', false));
        isSame('', \strip_tags((string)$csv->validate()));
    }

    public function testAggregateRuleCombo(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Float', 'sum', 4691.3235));
        isSame('', \strip_tags((string)$csv->validate()));

        $csv = new CsvFile(Tools::DEMO_CSV, Tools::getAggregateRule('Float', 'sum', 20));
        isSame(
            '"ag:sum" at line <red>1</red>, column "2:Float". The sum of numbers in the column is ' .
            '"<c>4691.3235</c>", which is not equal than the expected "<green>20</green>".' . "\n",
            (string)$csv->validate(),
        );
    }

    public function testCellRuleNoName(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule(null, 'not_empty', true));
        isSame(
            '"csv.header" at line 1, column "0:". Property "name" is not defined in schema: "_custom_array_".',
            $csv->validate()->render(cleanOutput: true),
        );
    }

    public function testQuickStop(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(1, $csv->validate(true)->count());

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(100, $csv->validate(false)->count());

        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame(100, $csv->validate()->count());
    }

    public function testErrorToArray(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, Tools::getRule('yn', 'is_email', true));
        isSame([
            'ruleCode'   => 'is_email',
            'message'    => 'Value "<c>N</c>" is not a valid email',
            'columnName' => '2:yn',
            'line'       => 2,
        ], $csv->validate(true)->get(0)->toArray());
    }

    public function testFilenamePattern(): void
    {
        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '/demo(-\\d+)?\\.csv$/']);
        isSame(
            '"filename_pattern". ' .
            'Filename "./tests/fixtures/complex_header.csv" does not match pattern: "/demo(-\d+)?\.csv$/".',
            \strip_tags((string)$csv->validate()->get(0)),
        );

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '']);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => null]);
        isSame('', (string)$csv->validate());

        $csv = new CsvFile(Tools::CSV_COMPLEX, ['filename_pattern' => '/.*\.csv$/']);
        isSame('', (string)$csv->validate());
    }

    public function testHeaderMatchingIfHeaderEnabled(): void
    {
        $columns = [
            ['name' => 'Name'],
            ['name' => 'City'],
            ['name' => 'Float'],
            // ['name' => 'Birthday'], // We skip it for tests
            ['name' => 'Favorite color'],
        ];

        $csv = new CsvFile(Tools::DEMO_CSV, ['csv' => ['header' => true], 'columns' => $columns]);

        isSame(['Name', 'City', 'Float', 'Birthday', 'Favorite color'], $csv->getHeader());
        isSame(['Name', 'City', 'Float', 'Favorite color'], $csv->getSchema()->getSchemaHeader());

        isSame([
            0 => 'Name',
            1 => 'City',
            2 => 'Float',
            4 => 'Favorite color', // 4 important here
        ], $csv->getColumnsMappedByHeaderNamesOnly());
    }

    public function testHeaderMatchingIfHeaderDisabled(): void
    {
        $columns = [
            ['name' => 'Name'],
            ['name' => 'City'],
            ['name' => 'Float'],
            // ['name' => 'Birthday'], // We skip it for tests
            ['name' => 'Favorite color'],
        ];

        $csv = new CsvFile(Tools::DEMO_CSV, ['csv' => ['header' => false], 'columns' => $columns]);

        isSame([0, 1, 2, 3, 4], $csv->getHeader());
        isSame(['Name', 'City', 'Float', 'Favorite color'], $csv->getSchema()->getSchemaHeader());

        isSame([
            0 => 'Name',
            1 => 'City',
            2 => 'Float',
            3 => 'Favorite color', // 3 important here
        ], $csv->getColumnsMappedByHeaderNamesOnly());
    }

    public function testHeaderMatchingIfHeaderEnabledExtraColumn(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'csv'              => ['header' => true],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'Optional column'], // Extra column
            ],
        ]);

        isSame(['Name', 'City', 'Float', 'Birthday', 'Favorite color'], $csv->getHeader());
        isSame(['Name', 'Optional column'], $csv->getSchema()->getSchemaHeader());

        isSame([0 => 'Name'], $csv->getColumnsMappedByHeaderNamesOnly());
    }

    public function testStrictColumnOrderValid(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(
            Tools::DEMO_CSV,
            ['columns' => [['name' => 'City'], ['name' => 'Float'], ['name' => 'Birthday']]],
        );
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, ['columns' => [['name' => 'City'], ['name' => 'Birthday']]]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, ['columns' => [['name' => 'City']]]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV);
        isSame(null, $csv->validate()->render());
    }

    public function testStrictColumnOrderInvalid(): void
    {
        $columns = [
            ['name' => 'City'],
            ['name' => 'Name'], // Wrong order here
            ['name' => 'Float'],
            ['name' => 'Birthday'],
            ['name' => 'Favorite color'],
        ];

        $csv = new CsvFile(Tools::DEMO_CSV, ['columns' => $columns]);

        isSame(
            '"strict_column_order" at line <red>1</red>. Real columns order doesn\'t match schema. ' .
            'Expected: <c>["Name", "City", "Float", "Birthday", "Favorite color"]</c>. ' .
            'Actual: <green>["City", "Name", "Float", "Birthday", "Favorite color"]</green>.' . "\n",
            $csv->validate()->render(),
        );

        $columns = [
            ['name' => 'City'],
            ['name' => 'Name'], // Wrong order here
            ['name' => 'Float'],
            ['name' => 'Favorite color'],
            ['name' => 'Birthday'],
            ['name' => 'Birthday'],
        ];

        $csv = new CsvFile(Tools::DEMO_CSV, ['columns' => $columns]);

        isSame(
            '"strict_column_order" at line <red>1</red>. Real columns order doesn\'t match schema. ' .
            'Expected: <c>["Name", "City", "Float", "Birthday", "Favorite color"]</c>. ' .
            'Actual: <green>["City", "Name", "Float", "Favorite color", "Birthday", "Birthday"]</green>.' . "\n",
            $csv->validate()->render(),
        );
    }

    public function testAllowExtraColumnsHeaderEnabled(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
            ],
        ]);
        isSame(
            '"required" at line 1, column "Schema Col Id: 5". Required column not found in CSV.',
            $csv->validate()->render(cleanOutput: true),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'              => ['header' => false],
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column', 'required' => false],
            ],
        ]);
        isSame(null, $csv->validate()->render(cleanOutput: true));

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => false],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
            ],
        ]);
        isSame(
            '"allow_extra_columns" at line 1. Column(s) not found in CSV: "Optional column".',
            $csv->validate()->render(cleanOutput: true),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => false],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
                ['name' => 'Optional column 2'],
            ],
        ]);
        isSame(
            '"allow_extra_columns" at line 1. Column(s) not found in CSV: ["Optional column", "Optional column 2"].',
            $csv->validate()->render(cleanOutput: true),
        );
    }

    public function testAllowExtraColumnsHeaderDisabled(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'     => ['header' => false],
            'columns' => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'              => ['header' => false],
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
            ],
        ]);
        isSame(
            '"required" at line 1, column "Schema Col Id: 5". Required column not found in CSV.',
            $csv->validate()->render(cleanOutput: true),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'              => ['header' => false],
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column', 'required' => false],
            ],
        ]);
        isSame(null, $csv->validate()->render(cleanOutput: true));

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'              => ['header' => false],
            'structural_rules' => ['allow_extra_columns' => false],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
            ],
        ]);
        isSame(
            '"allow_extra_columns" at line 1. Schema number of columns "6" greater than real "5".',
            $csv->validate()->render(cleanOutput: true),
        );

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'csv'              => ['header' => false],
            'structural_rules' => ['allow_extra_columns' => false],
            'columns'          => [
                ['name' => 'Name'],
                ['name' => 'City'],
                ['name' => 'Float'],
                ['name' => 'Birthday'],
                ['name' => 'Favorite color'],
                ['name' => 'Optional column'],
                ['name' => 'Optional column 2'],
            ],
        ]);
        isSame(
            '"allow_extra_columns" at line 1. Schema number of columns "7" greater than real "5".',
            $csv->validate()->render(cleanOutput: true),
        );
    }

    public function testRequiredColumnValid(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'Name', 'required' => true],
                ['name' => 'City', 'required' => true],
                ['name' => 'Float', 'required' => true],
                ['name' => 'Birthday', 'required' => true],
                ['name' => 'Favorite color', 'required' => true],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'columns' => [
                ['name' => 'Name', 'required' => false],
                ['name' => 'City', 'required' => false],
                ['name' => 'Float', 'required' => false],
                ['name' => 'Birthday', 'required' => false],
                ['name' => 'Favorite color', 'required' => false],
            ],
        ]);
        isSame(null, $csv->validate()->render());

        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],

            'columns' => [
                ['name' => 'Name', 'required' => true],
                ['name' => 'City', 'required' => true],
                ['name' => 'Float', 'required' => true],
                ['name' => 'Birthday', 'required' => true],
                ['name' => 'Favorite color', 'required' => true],
                ['name' => 'Optional column', 'required' => true],
                ['name' => 'Optional column 2', 'required' => true],
            ],
        ]);
        isSame(
            <<<'TEXT'
                "required" at line 1, column "Schema Col Id: 5". Required column not found in CSV.
                "required" at line 1, column "Schema Col Id: 6". Required column not found in CSV.
                TEXT,
            $csv->validate()->render(cleanOutput: true),
        );
    }

    public function testRequiredColumnInvalid(): void
    {
        // Extra. Required.
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                [
                    'name'     => 'Optinal column',
                    'required' => true,
                    'rules'    => ['not_empty' => true],
                ],
            ],
        ]);
        isSame([0 => 'Name'], $csv->getColumnsMappedByHeaderNamesOnly());
        isSame(
            '"required" at line 1, column "Schema Col Id: 1". Required column not found in CSV.',
            $csv->validate()->render(cleanOutput: true),
        );

        // Extra. Not required.
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                [
                    'name'     => 'Optinal column',
                    'required' => false,
                    'rules'    => ['not_empty' => true],
                ],
            ],
        ]);
        isSame([0 => 'Name'], $csv->getColumnsMappedByHeaderNamesOnly());
        isSame(null, $csv->validate()->render(cleanOutput: true));

        // Real. Required.
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                [
                    'name'     => 'Birthday',
                    'required' => false,
                    'rules'    => ['not_empty' => true],
                ],
            ],
        ]);
        isSame([0 => 'Name', 3 => 'Birthday'], $csv->getColumnsMappedByHeaderNamesOnly());
        isSame(null, $csv->validate()->render(cleanOutput: true));

        // Real. Not required.
        $csv = new CsvFile(Tools::DEMO_CSV, [
            'structural_rules' => ['allow_extra_columns' => true],
            'columns'          => [
                ['name' => 'Name'],
                [
                    'name'     => 'Birthday',
                    'required' => false,
                    'rules'    => ['not_empty' => true],
                ],
            ],
        ]);
        isSame([0 => 'Name', 3 => 'Birthday'], $csv->getColumnsMappedByHeaderNamesOnly());
        isSame(null, $csv->validate()->render(cleanOutput: true));
    }

    public function testInvalidRegex(): void
    {
        $csv = new CsvFile(Tools::DEMO_CSV, ['filename_pattern' => '/.*())))\.csv$/']);

        isSame(
            '"filename_pattern". Filename pattern error: Invalid regex: "/.*())))\.csv$/". ' .
            'Error: "preg_match(): Compilation failed: unmatched closing parenthesis at offset 4".',
            $csv->validate()->render(cleanOutput: true),
        );
    }
}
