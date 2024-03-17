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

use JBZoo\CsvBlueprint\Rules\Cell\IsBool;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsBoolTest extends AbstractCellRule
{
    protected string $ruleClass = IsBool::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('true'));
        isSame('', $rule->test('false'));
        isSame('', $rule->test('TRUE'));
        isSame('', $rule->test('FALSE'));
        isSame('', $rule->test('True'));
        isSame('', $rule->test('False'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1" is not allowed. Allowed values: ["true", "false"]',
            $rule->test('1'),
        );

        $rule = $this->create(true);
        isSame(
            'Value "" is not allowed. Allowed values: ["true", "false"]',
            $rule->test(''),
        );
    }

    public function testInvalidOption(): void
    {
        $this->expectExceptionMessage('Invalid option "qwerty" for the "is_bool" rule. It should be true|false.');

        $rule = $this->create('qwerty');
        $rule->validate('true');
    }
}
