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

use JBZoo\CsvBlueprint\Rules\Cell\IsUuid;
use JBZoo\PHPUnit\Rules\AbstractCellRule;
use JBZoo\Utils\Str;

use function JBZoo\PHPUnit\isSame;

final class IsUuid4Test extends AbstractCellRule
{
    protected string $ruleClass = IsUuid::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test(Str::uuid()));

        $rule = $this->create(false);
        isSame(null, $rule->validate('123'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value is not a valid UUID',
            $rule->test('123e4567-e89b-12d3-a456-4266554400zz'),
        );
        isSame(
            'Value is not a valid UUID',
            $rule->test('123'),
        );
    }
}
