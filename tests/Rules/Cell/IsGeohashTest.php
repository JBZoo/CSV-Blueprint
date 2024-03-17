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

use JBZoo\CsvBlueprint\Rules\Cell\IsGeohash;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsGeohashTest extends AbstractCellRule
{
    protected string $ruleClass = IsGeohash::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('u4pruydqqvj'));
        isSame('', $rule->test('u4pruydqqv'));
        isSame('', $rule->test('u4pruydqq'));
        isSame('', $rule->test('u4pruydq'));
        isSame('', $rule->test('u4pruyd'));
        isSame('', $rule->test('u4pruy'));
        isSame('', $rule->test('u4pru'));
        isSame('', $rule->test('u4pr'));
        isSame('', $rule->test('u4p'));
        isSame('', $rule->test('u4'));
        isSame('', $rule->test('u'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "Qwsad342323423erty" is not a valid Geohash',
            $rule->test('Qwsad342323423erty'),
        );
    }
}
