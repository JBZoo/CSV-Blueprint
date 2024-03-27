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

use JBZoo\CsvBlueprint\Rules\Cell\IsVersion;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsVersionTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsVersion::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '1.0.0',
            '1.0.0-alpha',
            '1.0.0-alpha.1',
            '1.0.0-0.3.7',
            '1.0.0-x.7.z.92',
            '1.3.7+build.2.b8f12d7',
            '1.3.7-rc.1',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = ['1', '1.3.7++', '1.3.7--', \uniqid('', true), '1.2.3.4', '1.2.3.4-beta', 'beta'];
        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" should be a valid semantic version. Example: \"1.2.3\"",
                $rule->test($value),
                $value,
            );
        }
    }
}
