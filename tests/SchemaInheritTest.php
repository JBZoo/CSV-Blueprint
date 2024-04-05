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

final class SchemaInheritTest extends TestCase
{
    public function testDefaults(): void
    {
        $schema = new Schema();
        isSame([
            'name'             => '',
            'description'      => '',
            'includes'         => [],
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
            'includes'         => [],
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
            'includes'         => [],
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
                    'rules'           => [],
                    'aggregate_rules' => [],
                ],
                [
                    'name'            => 'Second Column',
                    'description'     => '',
                    'example'         => null,
                    'required'        => false,
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
            'includes' => [
                'parent' => ['filename_pattern' => '/.*/i'],
            ],
            'filename_pattern' => [
                'inherit' => 'parent',
            ],
        ]);

        isSame('/.*/i', $schema->getData()->getString('filename_pattern'));
        isSame('', (string)$schema->validate());
    }

    public function testOverideCsvFull(): void
    {
        $schema = new Schema([
            'includes' => [
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
            'csv' => ['inherit' => 'parent'],
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
            'includes' => [
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
                'inherit'  => 'parent',
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
            'includes' => [
                'parent' => [
                    'structural_rules' => [
                        'strict_column_order' => false,
                        'allow_extra_columns' => true,
                    ],
                ],
            ],
            'structural_rules' => [
                'inherit' => 'parent',
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
            'includes' => [
                'parent' => [
                    'structural_rules' => [
                        'strict_column_order' => true,
                        'allow_extra_columns' => false,
                    ],
                ],
            ],
            'structural_rules' => [
                'inherit'             => 'parent',
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
            'includes'         => ['parent' => ['structural_rules' => []]],
            'structural_rules' => [
                'inherit'             => 'parent',
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
            'rules'           => ['not_empty' => true],
            'aggregate_rules' => ['sum' => 10],
        ];

        $parentColum1 = [
            'name'            => 'Name',
            'description'     => 'Another Description',
            'example'         => '234',
            'required'        => false,
            'rules'           => ['is_int' => true],
            'aggregate_rules' => ['sum_max' => 100],
        ];

        $schema = new Schema([
            'includes' => ['parent' => ['columns' => [$parentColum0, $parentColum1]]],
            'columns'  => [
                ['inherit' => 'parent/0'],
                ['inherit' => 'parent/1'],
                ['inherit' => 'parent/0:'],
                ['inherit' => 'parent/1:'],
                ['inherit' => 'parent/Name'],
                ['inherit' => 'parent/0:Name'],
                ['inherit' => 'parent/1:Name'],
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
            'rules'       => [
                'allow_values' => ['a', 'b', 'c'],
                'length_min'   => 1,
                'length'       => 5,
                'length_max'   => 10,
            ],
            'aggregate_rules' => ['sum_max' => 42],
        ];

        $schema = new Schema([
            'includes' => ['parent' => ['columns' => [$parentColum]]],
            'columns'  => [
                [
                    'inherit' => 'parent/Name',
                    'name'    => 'Child name',
                    'rules'   => [
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
            'includes' => ['parent' => ['columns' => [$parentColum]]],
            'columns'  => [
                [
                    'name'  => 'Child name',
                    'rules' => ['inherit' => 'parent/0:'],
                ],
            ],
        ]);

        isSame([
            [
                'name'        => 'Child name',          // Child
                'description' => '',                    // Default
                'example'     => null,                  // Default
                'required'    => true,                  // Default
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
            'includes' => ['parent' => ['columns' => [$parentColum]]],
            'columns'  => [
                [
                    'name'  => 'Child name',
                    'rules' => [
                        'inherit'      => 'parent/0:',
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
            'includes' => ['parent' => ['columns' => [$parentColum]]],
            'columns'  => [
                [
                    'name'            => 'Child name',
                    'aggregate_rules' => ['inherit' => 'parent/0:'],
                ],
            ],
        ]);

        isSame([
            [
                'name'            => 'Child name',          // Child
                'description'     => '',                    // Default
                'example'         => null,                  // Default
                'required'        => true,                  // Default
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
            'includes' => ['parent' => ['columns' => [$parentColum]]],
            'columns'  => [
                [
                    'name'            => 'Child name',
                    'aggregate_rules' => [
                        'inherit' => 'parent/0:',
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
        $schema = new Schema('./tests/schemas/inherit/parent.yml');
        isSame([
            'name'             => 'Parent schema',
            'description'      => 'Testing inheritance.',
            'includes'         => [],
            'filename_pattern' => '/parent-\d.csv$/i',
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
        $schema = new Schema('./tests/schemas/inherit/child.yml');
        isSame([
            'name'        => 'Child schema',
            'description' => 'Testing inheritance from parent schema.',
            'includes'    => [
                'parent' => PROJECT_ROOT . '/tests/schemas/inherit/parent.yml',
            ],
            'filename_pattern' => '/parent-\d.csv$/i',
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
                    'rules'           => [],
                    'aggregate_rules' => ['nth_num' => [4, 0.001]],
                ],
                8 => [
                    'name'        => 'Second Column',
                    'description' => 'Some number.',
                    'example'     => 123,
                    'required'    => false,
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
        $schema = new Schema('./tests/schemas/inherit/child-of-child.yml');
        isSame([
            'name'        => 'Child of child schema',
            'description' => 'Testing inheritance from child schema.',
            'includes'    => [
                'parent-1_0' => PROJECT_ROOT . '/tests/schemas/inherit/child.yml',
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
}
