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

namespace JBZoo\CsvBlueprint\Validators;

use JBZoo\CsvBlueprint\Csv\Column;
use JBZoo\CsvBlueprint\Rules\Ruleset;

final class ColumnValidator
{
    // This is a fallback line number for aggregate rules.
    // "1" - is a first line in the CSV file. It's always exists and usefeul for CI reports.
    public const FALLBACK_LINE = 1;

    private Ruleset $cellRuleset;
    private Ruleset $aggRuleset;

    public function __construct(Column $column)
    {
        $this->cellRuleset = new Ruleset($column->getRules(), $column->getHumanName());
        $this->aggRuleset  = new Ruleset($column->getAggregateRules(), $column->getHumanName());
    }

    public function validateCell(string $cellValue, int $line): ErrorSuite
    {
        return $this->cellRuleset->validateRuleSet($cellValue, $line);
    }

    public function validateList(array &$cellValue): ErrorSuite
    {
        return $this->aggRuleset->validateRuleSet($cellValue, self::FALLBACK_LINE);
    }
}
