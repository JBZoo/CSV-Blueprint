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

use JBZoo\CsvBlueprint\Schema;

final class SchemaPresetTest extends TestCase
{
    public function testDefaults(): void
    {
        $schema = new Schema();
        isSame([
            'name'             => '',
            'description'      => '',
            'presets'          => [],
            'filename_pattern' => '',
            'csv'              => [
                'header'     => true,
                'delimiter'  => ',',
                'quote_char' => '\\',
                'enclosure'  => '"',
                'encoding'   => 'utf-8',
                'bom'        => false,
            ],
            'structural_rules' => [
                'strict_column_order' => true,
                'allow_extra_columns' => false,
            ],
            'columns' => [],
        ], $schema->getData()->getArrayCopy());

        isSame('', (string)$schema->validate());
    }

    public function testOverideDefaults(): void
    {
        $schema = new Schema([
            'name'             => 'Qwerty',
            'description'      => 'Some description.',
            'presets'          => [],
            'filename_pattern' => '/.*/i',
            'csv'              => [
                'header'     => false,
                'delimiter'  => 'd',
                'quote_char' => 'q',
                'enclosure'  => 'e',
                'encoding'   => 'utf-16',
                'bom'        => true,
            ],
            'structural_rules' => [
                'strict_column_order' => false,
                'allow_extra_columns' => true,
            ],
            'columns' => [
                ['name' => 'Name', 'required' => true],
                ['name' => 'Second Column', 'required' => false],
            ],
        ]);

        isSame([
            'name'             => 'Qwerty',
            'description'      => 'Some description.',
            'presets'          => [],
            'filename_pattern' => '/.*/i',
            'csv'              => [
                'header'     => false,
                'delimiter'  => 'd',
                'quote_char' => 'q',
                'enclosure'  => 'e',
                'encoding'   => 'utf-16',
                'bom'        => true,
            ],
            'structural_rules' => [
                'strict_column_order' => false,
                'allow_extra_columns' => true,
            ],
            'columns' => [
                [
                    'name'            => 'Name',
                    'description'     => '',
                    'example'         => null,
                    'required'        => true,
                    'extra'           => null,
                    'rules'           => [],
                    'aggregate_rules' => [],
                ],
                [
                    'name'            => 'Second Column',
                    'description'     => '',
                    'example'         => null,
                    'required'        => false,
                    'extra'           => null,
                    'rules'           => [],
                    'aggregate_rules' => [],
                ],
            ],
        ], $schema->getData()->getArrayCopy());

        isSame('', (string)$schema->validate());
    }

    public function testOverideFilenamePattern(): void
    {
        $schema = new Schema([
            'presets' => [
                'parent' => ['filename_pattern' => '/.*/i'],
            ],
            'filename_pattern' => [
                'preset' => 'parent',
            ],
        ]);

        isSame('/.*/i', $schema->getData()->getString('filename_pattern'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideCsvFull(): void
    {
        $schema = new Schema([
            'presets' => [
                'parent' => [
                    'csv' => [
                        'header'     => false,
                        'delimiter'  => 'd',
                        'quote_char' => 'q',
                        'enclosure'  => 'e',
                        'encoding'   => 'utf-16',
                        'bom'        => true,
                    ],
                ],
            ],
            'csv' => ['preset' => 'parent'],
        ]);

        isSame([
            'header'     => false,
            'delimiter'  => 'd',
            'quote_char' => 'q',
            'enclosure'  => 'e',
            'encoding'   => 'utf-16',
            'bom'        => true,
        ], $schema->getData()->getArray('csv'));

        isSame('', (string)$schema->validate());
    }

    public function testOverideCsvPartial(): void
    {
        $schema = new Schema([
            'presets' => [
                'parent' => [
                    'csv' => [
                        'header'     => false,
                        'delimiter'  => 'd',
                        'quote_char' => 'q',
                        'bom'        => true,
                    ],
                ],
            ],
            'csv' => [
                'preset'   => 'parent',
                'encoding' => 'utf-32',
            ],
        ]);

        isSame([
            'header'     => false,          // parent value
            'delimiter'  => 'd',            // parent value
            'quote_char' => 'q',            // parent value
            'enclosure'  => '"',            // default value
            'encoding'   => 'utf-32',       // child value
            'bom'        => true,           // parent value
        ], $schema->getData()->getArray('csv'));

        isSame('', (string)$schema->validate());
    }

    public function testOverideStructuralRulesFull(): void
    {
        $schema = new Schema([
            'presets' => [
                'parent' => [
                    'structural_rules' => [
                        'strict_column_order' => false,
                        'allow_extra_columns' => true,
                    ],
                ],
            ],
            'structural_rules' => [
                'preset' => 'parent',
            ],
        ]);

        isSame([
            'strict_column_order' => false,
            'allow_extra_columns' => true,
        ], $schema->getData()->getArray('structural_rules'));

        isSame('', (string)$schema->validate());
    }

    public function testOverideStructuralRulesPartial1(): void
    {
        $schema = new Schema([
            'presets' => [
                'parent' => [
                    'structural_rules' => [
                        'strict_column_order' => true,
                        'allow_extra_columns' => false,
                    ],
                ],
            ],
            'structural_rules' => [
                'preset'              => 'parent',
                'allow_extra_columns' => true,
            ],
        ]);

        isSame([
            'strict_column_order' => true,  // parent value
            'allow_extra_columns' => true,  // child value
        ], $schema->getData()->getArray('structural_rules'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideStructuralRulesPartial2(): void
    {
        $schema = new Schema([
            'presets'          => ['parent' => ['structural_rules' => []]],
            'structural_rules' => [
                'preset'              => 'parent',
                'allow_extra_columns' => true,
            ],
        ]);

        isSame([
            'strict_column_order' => true, // default value
            'allow_extra_columns' => true, // parent value
        ], $schema->getData()->getArray('structural_rules'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnFull(): void
    {
        $parentColum0 = [
            'name'            => 'Name',
            'description'     => 'Description',
            'example'         => '123',
            'required'        => false,
            'extra'           => null,
            'rules'           => ['not_empty' => true],
            'aggregate_rules' => ['sum' => 10],
        ];

        $parentColum1 = [
            'name'            => 'Name',
            'description'     => 'Another Description',
            'example'         => '234',
            'required'        => false,
            'extra'           => null,
            'rules'           => ['is_int' => true],
            'aggregate_rules' => ['sum_max' => 100],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum0, $parentColum1]]],
            'columns' => [
                ['preset' => 'parent/0'],
                ['preset' => 'parent/1'],
                ['preset' => 'parent/0:'],
                ['preset' => 'parent/1:'],
                ['preset' => 'parent/Name'],
                ['preset' => 'parent/0:Name'],
                ['preset' => 'parent/1:Name'],
            ],
        ]);

        isSame([
            $parentColum0,
            $parentColum1,
            $parentColum0,
            $parentColum1,
            $parentColum0,
            $parentColum0,
            $parentColum1,
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnPartial(): void
    {
        $parentColum = [
            'name'        => 'Name',
            'description' => 'Description',
            'extra'       => ['123', '456'],
            'rules'       => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => ['sum_max' => 42],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum]]],
            'columns' => [
                [
                    'preset' => 'parent/Name',
                    'name'   => 'Child name',
                    'rules'  => [
                        'is_int'       => true,
                        'length_min'   => 2,
                        'length'       => 5,
                        'allow_values' => ['c'],
                    ],
                ],
            ],
        ]);

        isSame([
            [
                'name'        => 'Child name',              // Child
                'description' => 'Description',             // Parent
                'example'     => null,                      // Default
                'required'    => true,                      // Default
                'extra'       => ['123', '456'],            // Parent
                'rules'       => [
                    'allow_values' => ['c'],                // Child
                    'length_min'   => 2,                    // Child
                    'length'       => 5,                    // Parent
                    'length_max'   => 10,                   // Parent
                    'is_int'       => true,                 // Child
                ],
                'aggregate_rules' => ['sum_max' => 42],     // Parent
            ],
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnRulesFull(): void
    {
        $parentColum = [
            'rules' => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => [
                'sum_max'   => 42,
                'is_unique' => true,
            ],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum]]],
            'columns' => [
                [
                    'name'  => 'Child name',
                    'rules' => ['preset' => 'parent/0:'],
                ],
            ],
        ]);

        isSame([
            [
                'name'        => 'Child name',          // Child
                'description' => '',                    // Default
                'example'     => null,                  // Default
                'required'    => true,                  // Default
                'extra'       => null,                  // Default
                'rules'       => [                      // Parent All
                    'allow_values' => ['a', 'b', 'c'],
                    'length_min'   => 1,
                    'length'       => 5,
                    'length_max'   => 10,
                ],
                'aggregate_rules' => [],                // Default
            ],
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnRulesPartial(): void
    {
        $parentColum = [
            'rules' => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => [
                'sum_max'   => 42,
                'is_unique' => true,
            ],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum]]],
            'columns' => [
                [
                    'name'  => 'Child name',
                    'rules' => [
                        'preset'       => 'parent/0:',
                        'allow_values' => ['d', 'c'],
                        'length_max'   => 100,
                    ],
                ],
            ],
        ]);

        isSame([
            [
                'name'        => 'Child name',          // Child
                'description' => '',                    // Default
                'example'     => null,                  // Default
                'required'    => true,                  // Default
                'extra'       => null,                  // Default
                'rules'       => [
                    'allow_values' => ['d', 'c'],       // Child
                    'length_min'   => 1,                // Parent
                    'length'       => 5,                // Parent
                    'length_max'   => 100,              // Child
                ],
                'aggregate_rules' => [],                // Default
            ],
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnAggregateRulesFull(): void
    {
        $parentColum = [
            'rules' => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => [
                'sum_max'   => 42,
                'is_unique' => true,
            ],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum]]],
            'columns' => [
                [
                    'name'            => 'Child name',
                    'aggregate_rules' => ['preset' => 'parent/0:'],
                ],
            ],
        ]);

        isSame([
            [
                'name'            => 'Child name',          // Child
                'description'     => '',                    // Default
                'example'         => null,                  // Default
                'required'        => true,                  // Default
                'extra'           => null,                  // Default
                'rules'           => [],                    // default
                'aggregate_rules' => [                      // Parent All
                    'sum_max'   => 42,
                    'is_unique' => true,
                ],
            ],
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideColumnAggregateRulesPartial(): void
    {
        $parentColum = [
            'rules' => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => [
                'sum_max'   => 42,
                'is_unique' => true,
            ],
        ];

        $schema = new Schema([
            'presets' => ['parent' => ['columns' => [$parentColum]]],
            'columns' => [
                [
                    'name'            => 'Child name',
                    'aggregate_rules' => [
                        'preset'  => 'parent/0:',
                        'sum_max' => 4200,
                        'sum_min' => 1,
                    ],
                ],
            ],
        ]);

        isSame([
            [
                'name'            => 'Child name',          // Child
                'description'     => '',                    // Default
                'example'         => null,                  // Default
                'required'        => true,                  // Default
                'extra'           => null,                  // Default
                'rules'           => [],                    // default
                'aggregate_rules' => [
                    'sum_max'   => 4200,                    // Child
                    'is_unique' => true,                    // Parent
                    'sum_min'   => 1,                       // Child
                ],
            ],
        ], $schema->getData()->getArray('columns'));
        isSame('', (string)$schema->validate());
    }

    public function testRealParent(): void
    {
        $schema = new Schema('./tests/schemas/preset/parent.yml');
        isSame([
            'name'             => 'Parent schema',
            'description'      => '',
            'presets'          => [],
            'filename_pattern' => '/preset-\d.csv$/i',
            'csv'              => [
                'header'     => false,
                'delimiter'  => 'd',
                'quote_char' => 'q',
                'enclosure'  => 'e',
                'encoding'   => 'utf-16',
                'bom'        => true,
            ],
            'structural_rules' => [
                'strict_column_order' => false,
                'allow_extra_columns' => true,
            ],
            'columns' => [
                [
                    'name'        => 'Name',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => [
                        'nth_num' => [4, 0.001],
                    ],
                ],
                [
                    'name'        => 'Second Column',
                    'description' => 'Some number.',
                    'example'     => 123,
                    'required'    => false,
                    'extra'       => null,
                    'rules'       => [
                        'length_min' => 1,
                        'length_max' => 4,
                    ],
                    'aggregate_rules' => [
                        'sum' => 1000,
                    ],
                ],
            ],
        ], $schema->getData()->getArrayCopy());
        isSame('', (string)$schema->validate());
    }

    public function testRealChild(): void
    {
        $schema = new Schema('./tests/schemas/preset/child.yml');
        isSame([
            'name'        => 'Child schema',
            'description' => '',
            'presets'     => [
                'preset' => PROJECT_ROOT . '/tests/schemas/preset/parent.yml',
            ],
            'filename_pattern' => '/preset-\d.csv$/i',
            'csv'              => [
                'header'     => true,
                'delimiter'  => 'd',
                'quote_char' => 'q',
                'enclosure'  => 'e',
                'encoding'   => 'utf-16',
                'bom'        => true,
            ],
            'structural_rules' => [
                'strict_column_order' => true,
                'allow_extra_columns' => true,
            ],
            'columns' => [
                0 => [
                    'name'        => 'Name',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                1 => [
                    'name'        => 'Overridden name by column name',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                2 => [
                    'name'        => 'Overridden name by column index',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                3 => [
                    'name'        => 'Overridden name by column index and column name',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                4 => [
                    'name'        => 'Overridden name by column index and column name + added rules',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 1,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                5 => [
                    'name'        => 'Overridden name by column index and column name + added aggregate rules',
                    'description' => 'Full name of the person.',
                    'example'     => 'John D',
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => ['nth_num' => [10, 0.05]],
                ],
                6 => [
                    'name'        => 'Overridden only rules',
                    'description' => '',
                    'example'     => null,
                    'required'    => true,
                    'extra'       => null,
                    'rules'       => [
                        'not_empty'  => true,
                        'length_min' => 5,
                        'length_max' => 7,
                    ],
                    'aggregate_rules' => [],
                ],
                7 => [
                    'name'            => 'Overridden only aggregation rules',
                    'description'     => '',
                    'example'         => null,
                    'required'        => true,
                    'extra'           => null,
                    'rules'           => [],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                8 => [
                    'name'        => 'Second Column',
                    'description' => 'Some number.',
                    'example'     => 123,
                    'required'    => false,
                    'extra'       => null,
                    'rules'       => [
                        'length_min' => 1,
                        'length_max' => 4,
                    ],
                    'aggregate_rules' => ['sum' => 1000],
                ],
            ],
        ], $schema->getData()->getArrayCopy());
        isSame('', (string)$schema->validate());
    }

    public function testRealChildOfChild(): void
    {
        $schema = new Schema('./tests/schemas/preset/child-of-child.yml');
        isSame([
            'name'        => 'Child of child schema',
            'description' => '',
            'presets'     => [
                'preset-1' => PROJECT_ROOT . '/tests/schemas/preset/child.yml',
            ],
            'filename_pattern' => '/child-of-child-\d.csv$/i',
            'csv'              => [
                'header'     => true,
                'delimiter'  => 'dd',
                'quote_char' => 'qq',
                'enclosure'  => 'ee',
                'encoding'   => 'utf-32',
                'bom'        => false,
            ],
            'structural_rules' => [
                'strict_column_order' => true,
                'allow_extra_columns' => false,
            ],
            'columns' => [
                [
                    'name'        => 'Second Column',
                    'description' => 'Some number.',
                    'example'     => 123,
                    'required'    => false,
                    'extra'       => null,
                    'rules'       => [
                        'length_min' => 1,
                        'length_max' => 4,
                    ],
                    'aggregate_rules' => ['sum' => 1000],
                ],
            ],
        ], $schema->getData()->getArrayCopy());
        isSame('', (string)$schema->validate());
    }

    public function testInvalidPresetFile(): void
    {
        $this->expectExceptionMessage(
            "Invalid schema \"undefined\" data.\n"
            . 'Unexpected error: "Unknown included file: "invalid.yml""',
        );

        $schema = new Schema(['presets' => ['alias' => 'invalid.yml']]);
    }
}
