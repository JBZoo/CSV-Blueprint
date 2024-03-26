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

use JBZoo\CsvBlueprint\Rules\Cell\IsAngle;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsAngleTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsAngle::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test('0'));
        isSame('', $rule->test('90'));
        isSame('', $rule->test('360.0'));
        isSame('', $rule->test('360'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('90.1.1.1.1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1230" is not a valid angle (0 to 360)',
            $rule->test('1230'),
        );
        isSame(
            'Value "-0.1" is not a valid angle (0 to 360)',
            $rule->test('-0.1'),
        );
        isSame(
            'Value "90.1.1.1.1" is not a float number',
            $rule->test('90.1.1.1.1'),
        );
    }
}
