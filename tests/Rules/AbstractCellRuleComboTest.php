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

use JBZoo\CsvBlueprint\Rules\AbstractCombo;
use JBZoo\CsvBlueprint\Rules\AbstractCombo as Combo;
use JBZoo\PHPUnit\TestCase;

use function JBZoo\PHPUnit\isFileContains;

abstract class AbstractCellRuleComboTest extends TestCase
{
    protected string $ruleClass = '';

    abstract public function testEqual(): void;

    abstract public function testNotEqual(): void;

    abstract public function testMin(): void;

    abstract public function testMax(): void;

    abstract public function testInvalidOption(): void;

    abstract public function testInvalidParsing(): void;

    public function testHelpMessageInExample(): void
    {
        isFileContains($this->create(6, Combo::MAX)->getHelp(), PROJECT_ROOT . '/schema-examples/full.yml');
    }

    protected function create(array|float|int|string $value, string $mode): AbstractCombo
    {
        return new $this->ruleClass('prop', $value, $mode);
    }
}
