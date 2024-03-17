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

use JBZoo\CsvBlueprint\Rules\Cell\IsBase64;
use JBZoo\PHPUnit\Rules\AbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsBase64Test extends AbstractCellRule
{
    protected string $ruleClass = IsBase64::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('cmVzcGVjdCE='));

        $rule = $this->create(false);
        isSame(null, $rule->validate('1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);
        isSame(
            'Value is not a valid Base64',
            $rule->test('Hello world!'),
        );
    }
}
