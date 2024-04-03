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

use JBZoo\CsvBlueprint\Rules\Cell\IsIsbn;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsIsbnTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsIsbn::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            'ISBN-13: 978-0-596-52068-7',
            '978 0 596 52068 7',
            '9780596520687',
            '0-596-52068-9',
            '0 512 52068 9',
            'ISBN-10 0-596-52068-9',
            'ISBN-10: 0-596-52068-9',
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), "\"{$value}\"");
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ';',
            '!@#$%^&*()',
            'ISBN 11978-0-596-52068-7',
            'ISBN-12: 978-0-596-52068-7',
            '978 10 596 52068 7',
            '119780596520687',
            '0-5961-52068-9',
            '11 5122 52068 9',
            'ISBN-11 0-596-52068-9',
            'ISBN-10- 0-596-52068-9',
            'Defiatly no ISBN',
            'Neither ISBN-13: 978-0-596-52068-7',
        ];

        foreach ($invalid as $value) {
            isSame(
                "Value \"{$value}\" is not a valid ISBN number.",
                $rule->test($value),
                $value,
            );
        }
    }
}
