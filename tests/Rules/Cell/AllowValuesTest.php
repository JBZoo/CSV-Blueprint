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

use JBZoo\CsvBlueprint\Rules\Cell\AllowValues;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class AllowValuesTest extends AbstractCellRuleTest
{
    protected string $ruleClass = AllowValues::class;

    public function testPositive(): void
    {
        $rule = $this->create(['1', '2', '3']);
        isSame(null, $rule->validate('1'));
        isSame(null, $rule->validate('2'));
        isSame(null, $rule->validate('3'));

        $rule = $this->create(['1', '2', '3', '']);
        isSame(null, $rule->validate(''));

        $rule = $this->create(['1', '2', '3', ' ']);
        isSame(null, $rule->validate(' '));
    }

    public function testNegative(): void
    {
        $rule = $this->create(['1', '2', '3']);

        isSame(
            '"allow_values" at line 1, column "prop". ' .
            'Value "invalid" is not allowed. Allowed values: ["1", "2", "3"].',
            \strip_tags((string)$rule->validate('invalid')),
        );
    }

    public function testInvalidOption(): void
    {
    }

    public function testInvalidParsing(): void
    {
    }
}
