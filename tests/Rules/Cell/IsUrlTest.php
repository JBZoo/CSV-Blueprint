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

use JBZoo\CsvBlueprint\Rules\Cell\IsUrl;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsUrlTest extends AbstractCellRule
{
    protected string $ruleClass = IsUrl::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('http://example.com'));
        isSame('', $rule->test('http://example.com/home-page'));
        isSame('', $rule->test('ftp://user:pass@example.com/home-page?param=value&v=asd#anchor'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('//example.com'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value "123" is not a valid URL',
            $rule->test('123'),
        );
        isSame(
            'Value "//example.com" is not a valid URL',
            $rule->test('//example.com'),
        );
    }
}
