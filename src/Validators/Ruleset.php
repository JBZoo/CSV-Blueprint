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

use JBZoo\CsvBlueprint\CellRules\AbstarctCellRule;
use JBZoo\CsvBlueprint\Utils;

final class Ruleset
{
    /** @var AbstarctCellRule[] */
    private array  $rules;
    private string $columnNameId;

    public function __construct(array $rules, string $columnNameId)
    {
        $this->columnNameId = $columnNameId;
        $this->rules        = [];

        foreach ($rules as $ruleName => $options) {
            $this->rules[] = $this->createRule((string)$ruleName, $options);
        }
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    public function createRule(string $ruleName, null|array|bool|float|int|string $options = null): AbstarctCellRule
    {
        $classname = '\\JBZoo\\CsvBlueprint\\CellRules\\' . Utils::kebabToCamelCase($ruleName);
        if (\class_exists($classname)) {
            // @phpstan-ignore-next-line
            return new $classname($this->columnNameId, $options);
        }

        throw new Exception("Rule \"{$ruleName}\" not found. Expected class: \"{$classname}\"");
    }

    public function validate(string $cellValue, int $line): ErrorSuite
    {
        $errors = new ErrorSuite();

        foreach ($this->rules as $rule) {
            $errors->addError($rule->validate($cellValue, $line));
        }

        return $errors;
    }
}
