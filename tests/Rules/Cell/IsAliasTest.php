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

use JBZoo\CsvBlueprint\Rules\Cell\IsAlias;
use JBZoo\PHPUnit\Rules\AbstractCellRuleTest;

use function JBZoo\PHPUnit\isSame;

final class IsAliasTest extends AbstractCellRuleTest
{
    protected string $ruleClass = IsAlias::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame('', $rule->test(''));
        isSame('', $rule->test('123'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('Qwerty, asd 123'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "Qwerty, asd 123" is not a valid alias. Expected "qwerty-asd-123".',
            $rule->test('Qwerty, asd 123'),
        );
    }
}
