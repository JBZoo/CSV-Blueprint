<?php

declare(strict_types=1);

/**
 * Item8 | JBZoo - Csv-Blueprint.
 *
 * This file is part of the Unilead Service Package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license     Proprietary
 * @copyright   Copyright (C) Unilead Network,  All rights reserved.
 * @see         https://www.unileadnetwork.com
 */

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\AbstractCombo;
use PHPUnit\Framework\TestCase;

abstract class AbstractComboTest extends TestCase
{
    protected string $ruleClass = '';

    abstract public function testGetHelp(): void;

    abstract public function testEqual(): void;

    abstract public function testMin(): void;

    abstract public function testMax(): void;

    abstract public function testNotEqual(): void;

    abstract public function testInvalidOption(): void;

    abstract public function testInvalidParsing(): void;

    protected function create(float|int|string $value): AbstractCombo
    {
        return new $this->ruleClass('prop', $value);
    }
}
