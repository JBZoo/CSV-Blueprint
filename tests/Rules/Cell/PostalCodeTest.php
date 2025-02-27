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

use JBZoo\CsvBlueprint\Rules\Cell\PostalCode;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class PostalCodeTest extends TestAbstractCellRule
{
    protected string $ruleClass = PostalCode::class;

    public function testPositive(): void
    {
        $rule = $this->create('BR');
        isSame('', $rule->test(''));
        isSame('', $rule->test('02179000'));
        isSame('', $rule->test('02179-000'));
    }

    public function testNegative(): void
    {
        $rule = $this->create('BR');
        isSame(
            'Value "qwerty" is not a valid postal code for country "BR".',
            $rule->test('qwerty'),
        );

        $rule = $this->create('QQ');
        isSame(
            '"postal_code" at line <red>1</red>, column "prop". '
            . 'Unexpected error: Cannot validate postal code from "QQ" country.',
            (string)$rule->validate('qwerty'),
        );
    }
}
