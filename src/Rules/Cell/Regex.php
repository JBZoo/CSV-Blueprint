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

use JBZoo\CsvBlueprint\Utils;

final class Regex extends AbstractCellRule
{
    protected const HELP_TOP = [
        'Any valid regex pattern. See https://www.php.net/manual/en/reference.pcre.pattern.syntax.php',
        "Of course it's a super powerful tool to verify any sort of string data.",
        'Please, be careful. Regex is a powerful tool, but it can be very dangerous if used incorrectly.',
        'Remember that if you want to solve a problem with regex, you now have two problems.',
        'But have it your way, then happy debugging! https://regex101.com',
    ];

    protected const HELP_OPTIONS = [
        self::DEFAULT => ['/^[\d]{2}$/'],
    ];

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $regex = Utils::prepareRegex($this->getOptionAsString());
        if ($regex === null || $regex === '') {
            return 'Regex pattern is not defined';
        }

        if (Utils::testRegex($regex, $cellValue)) {
            return "Value \"<c>{$cellValue}</c>\" does not match the pattern \"<green>{$regex}</green>\"";
        }

        return null;
    }
}
