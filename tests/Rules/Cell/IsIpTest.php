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

use JBZoo\CsvBlueprint\Rules\Cell\IsIp4;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIpTest extends AbstractCellRule
{
    protected string $ruleClass = IsIp4::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('127.0.0.1'));
        isSame('', $rule->test('0.0.0.0'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "1.2.3" is not a valid IP',
            $rule->test('1.2.3'),
        );
    }
}
