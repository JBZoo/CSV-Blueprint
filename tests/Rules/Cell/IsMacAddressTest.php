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

use JBZoo\CsvBlueprint\Rules\Cell\IsMacAddress;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsMacAddressTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsMacAddress::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('00:11:22:33:44:55'));
        isSame('', $rule->test('af-AA-22-33-44-55'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "127.0.0.1" is not a valid MAC address.',
            $rule->test('127.0.0.1'),
        );
    }
}
