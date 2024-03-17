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

namespace JBZoo\PHPUnit\Rules;

use JBZoo\CsvBlueprint\Rules\Cell\AbstractCellRule as CellRule;
use JBZoo\PHPUnit\TestCase;
use JBZoo\PHPUnit\Tools;
use JBZoo\Utils\Str;

use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;
use function JBZoo\PHPUnit\success;

abstract class AbstractCellRule extends TestCase
{
    protected string $ruleClass = '';

    abstract public function testPositive(): void;

    abstract public function testNegative(): void;

    // abstract public function testInvalidOption(): void;

    // abstract public function testInvalidParsing(): void;

    public function testHelpMessageInExample(): void
    {
        isFileContains($this->create(6)->getHelp(), Tools::SCHEMA_FULL);
    }

    public function testBoolenOptionFlag(): void
    {
        if (\str_starts_with($this->create(true)->getRuleCode(''), 'is_')) {
            // Enabled and ignore empty string
            $rule = $this->create(true);
            isSame(null, $rule->validate(''));

            // Disabled and ignore ANY string
            $rule = $this->create(false);
            isSame(null, $rule->validate(Str::random(10)));
        }

        success();
    }

    protected function create(array|bool|float|int|string $value): CellRule
    {
        return new $this->ruleClass('prop', $value);
    }
}
