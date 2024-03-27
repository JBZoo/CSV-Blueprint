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

use JBZoo\CsvBlueprint\Rules\Cell\IsRoman;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsRomanTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsRoman::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            'III',
            'IV',
            'VI',
            'XIX',
            'XLII',
            'LXII',
            'CXLIX',
            'CLIII',
            'MCCXXXIV',
            'MMXXIV',
            'MCMLXXV',
            'MMMMCMXCIX',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ' ',
            'IIII',
            'IVVVX',
            'CCDC',
            'MXM',
            'XIIIIIIII',
            'MIMIMI',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" should contain only Roman numeral. Example: \"I\", \"IV\", \"XX\"",
                $rule->test($value),
                $value,
            );
        }
    }
}
