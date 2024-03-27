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

use JBZoo\CsvBlueprint\Rules\Cell\IsPublicDomainSuffix;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsPublicDomainSuffixTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsPublicDomainSuffix::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('com'));
        isSame('', $rule->test('CO.UK'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'The value "127.0.0.1" is not a valid public domain suffix. Example: "com", "nom.br", "net" etc.',
            $rule->test('127.0.0.1'),
        );
        isSame(
            'The value "invalid.com" is not a valid public domain suffix. Example: "com", "nom.br", "net" etc.',
            $rule->test('invalid.com'),
        );
    }
}
