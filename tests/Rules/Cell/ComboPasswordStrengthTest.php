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

use JBZoo\CsvBlueprint\Rules\AbstractRule as Combo;
use JBZoo\CsvBlueprint\Rules\Cell\ComboPasswordStrength;
use JBZoo\PHPUnit\Rules\TestAbstractCellRuleCombo;

use function JBZoo\PHPUnit\isSame;

class ComboPasswordStrengthTest extends TestAbstractCellRuleCombo
{
    protected string $ruleClass = ComboPasswordStrength::class;

    public function testEqual(): void
    {
        $rule = $this->create(2, Combo::MIN);

        isSame('', $rule->test(''));
        isSame('', $rule->test('123456'));
        isSame(
            'The password strength of the value "12345" is 1, which is less than the expected "2"',
            $rule->test('12345'),
        );
    }

    public function testScoring(): void
    {
        $dataset = [
            ''                      => 0,
            '1'                     => 0,
            '12'                    => 0,
            '123'                   => 0,
            '1234'                  => 0,
            '12345'                 => 1,
            '123456'                => 2,
            '1234567'               => 2,
            '12345678'              => 2,
            'q1'                    => 2,
            'q123'                  => 2,
            'q1['                   => 3,
            'qwerty'                => 0,
            'password'              => 1,
            'password123'           => 2,
            'password123{'          => 3,
            'Password123{'          => 4,
            'Passssword123{'        => 4,
            'paaasd123'             => 5,
            'Paaasd123'             => 6,
            '[aaasd123]'            => 7,
            'Paaasd123]'            => 8,
            'u@jXJ4&'               => 8,
            '9RfzE$8NKD'            => 9,
            'u@jXJ4&f7K'            => 9,
            'u@jXJ4&f7Ku@jXJ4&f7K'  => 9,
            'u@jXJ4&f7K u@jXJ4&f7K' => 10,
            'Lorem ipsum dolor 1@'  => 10,
        ];

        foreach ($dataset as $password => $expected) {
            $score = ComboPasswordStrength::passwordScore('' . $password);
            // echo "'{$password}' => {$score},\n";
            isSame($expected, $score, "\"{$password}\"");
        }
    }
}
