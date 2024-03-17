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

use function JBZoo\PHPUnit\isFileContains;

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

    protected function create(array|bool|float|int|string $value): CellRule
    {
        return new $this->ruleClass('prop', $value);
    }
}