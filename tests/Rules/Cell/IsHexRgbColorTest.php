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

use JBZoo\CsvBlueprint\Rules\Cell\IsHexRgbColor;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsHexRgbColorTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsHexRgbColor::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            '#000',
            '#00000F',
            '#00000f',
            '#123',
            '#123456',
            '#FFFFFF',
            '#ffffff',
            '123123',
            'FFFFFF',
            'ffffff',
            '443',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), "\"{$value}\"");
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ';',
            '!@#$%^&*()',
            '#0',
            '#0000G0',
            '#0FG',
            '#1234',
            '#AAAAAA1',
            '#S',
            '1234',
            'foo',
        ];

        foreach ($invalid as $value) {
            isSame(
                "Value \"{$value}\" is not a valid hex RGB color.",
                $rule->test($value),
                $value,
            );
        }
    }
}
