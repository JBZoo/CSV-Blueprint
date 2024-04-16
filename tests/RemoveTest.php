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

use JBZoo\CsvBlueprint\SchemaDataPrep;

final class RemoveTest extends TestCase
{
    public function test(): void
    {
        isSame(
            [
                'is_int'   => true,
                'is_float' => true,
            ],
            SchemaDataPrep::deleteUnnecessaryRules([
                'is_int'   => true,
                'is_float' => true,
            ]),
        );

        isSame(
            [
                'is_int'  => true,
                'num_min' => 1,
                'num_max' => 100,
            ],
            SchemaDataPrep::deleteUnnecessaryRules([
                'is_int'     => true,
                'is_float'   => true,
                'precision'  => 0,
                'num_min'    => 1,
                'num_max'    => 100,
                'length_min' => 1,
                'length_max' => 3,
            ]),
        );

        isSame(
            [
                'is_int'  => true,
                'num_min' => 1,
                'num_max' => 100,
            ],
            SchemaDataPrep::deleteUnnecessaryRules([
                'is_int'     => true,
                'is_float'   => true,
                'precision'  => 0,
                'num_min'    => 1,
                'num_max'    => 100,
                'length_min' => 1,
                'length_max' => 3,
            ]),
        );
    }
}
