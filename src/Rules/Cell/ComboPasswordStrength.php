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

namespace JBZoo\CsvBlueprint\Rules\Cell;

final class ComboPasswordStrength extends AbstractCellRuleCombo
{
    protected const NAME = 'password strength';

    public function getHelpMeta(): array
    {
        return [
            [
                'Password strength calculation criteria include: Length (max 5 points, +1 every 2 characters),',
                'presence of uppercase letters (+1), lowercase letters (+1), numbers (+1), special characters (+1),',
                'spaces (+1), and penalties for consecutive sequences of uppercase, lowercase, or',
                'numbers (-0.5 each), repetitive sequences (-0.75 each), common weak passwords like "qwerty",',
                'and passwords under 6 characters (-2). Adjust scores to a 0 to 10 scale, with a minimum score of 0.',
            ],
            [
                self::MIN     => ['1', 'x >= 1'],
                self::GREATER => ['2', 'x >  2'],
                self::NOT     => ['0', 'x != 0'],
                self::EQ      => ['7', 'x == 7'],
                self::LESS    => ['8', 'x <  8'],
                self::MAX     => ['9', 'x <= 9'],
            ],
        ];
    }

    public static function passwordScore(string $password): int
    {
        $score = 0;

        // Length: +1 point for every 2 characters, max 5 points
        $score += \min(5, \strlen($password) / 2);

        // Uppercase letters: +1 point if at least one
        if (\preg_match('/[A-Z]/', $password) !== 0) {
            $score++;
        }

        // Lowercase letters: +1 point if at least one
        if (\preg_match('/[a-z]/', $password) !== 0) {
            $score++;
        }

        // Numbers: +1 point if at least one
        if (\preg_match('/[0-9]/', $password) !== 0) {
            $score++;
        }

        // Special characters: +1 point if at least one
        if (\preg_match('/[^a-zA-Z0-9]/', $password) !== 0) {
            $score++;
        }

        if (\str_contains($password, ' ')) {
            $score++;
        }

        // Additional complexity: consecutive uppercase, lowercase, or numerical sequences
        // Deduct -0.75 point for each found, minimum score is 0
        $deductions = 0;
        if (\preg_match('/(.)\1+/', $password) !== 0) {
            $deductions++;
        }

        $deductions += \preg_match_all('/01|12|23|34|45|56|67|78|89|90/', $password);

        $deductions += \preg_match_all(
            '/ab|bc|cd|de|ef|fg|gh|hi|ij|jk|kl|lm|mn|no|op|pq|qr|rs|st|tu|uv|vw|wx|xy|yz/i',
            $password,
        );

        if (\preg_match('/qwerty|pass|password/i', $password) !== 0) {
            $deductions += 5;
        }

        $minLength = 6;
        if (\strlen($password) < $minLength) {
            $deductions += 2;
        }

        $score -= ($deductions * 0.75);
        $score = \max(0, $score); // Ensure score does not go below 0

        // Adjust to fit into 0 to 10 scale
        return (int)\round($score, 0);
    }

    protected function getExpected(): float
    {
        return $this->getOptionAsInt();
    }

    protected function getActualCell(string $cellValue): float
    {
        return self::passwordScore($cellValue);
    }
}
