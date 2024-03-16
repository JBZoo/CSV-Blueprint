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

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\Cell\IsLongitude;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class IsLongitudeTest extends AbstractCellRuleTest
{
    protected string $ruleClass = IsLongitude::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('0'));
        isSame('', $rule->test('180'));
        isSame('', $rule->test('-180'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1.0.0.0'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test('-180'));
        isSame(
            'Value "1230" is not a valid longitude (-180 -> 180)',
            $rule->test('1230'),
        );
        isSame(
            'Value "180.0001" is not a valid longitude (-180 -> 180)',
            $rule->test('180.0001'),
        );
        isSame(
            'Value "-180.1" is not a valid longitude (-180 -> 180)',
            $rule->test('-180.1'),
        );
        isSame(
            'Value "1.0.0.0" is not a float number',
            $rule->test('1.0.0.0'),
        );
    }
}
