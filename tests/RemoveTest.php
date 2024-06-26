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

use JBZoo\CsvBlueprint\Analyze\RuleOptimizer;

final class RemoveTest extends TestCase
{
    public function test(): void
    {
        isSame(
            [
                'is_int' => true,
            ],
            RuleOptimizer::optimize([
                'is_int'   => true,
                'is_float' => true,
            ]),
        );

        isSame(
            [
                'is_float'  => true,
                'precision' => 1,
            ],
            RuleOptimizer::optimize([
                'is_int'    => true,
                'is_float'  => true,
                'precision' => 1,
            ]),
        );
        // Need to add more tests
    }
}
