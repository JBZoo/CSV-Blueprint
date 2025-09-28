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

namespace JBZoo\CsvBlueprint\Rules;

use JBZoo\CsvBlueprint\Rules\Cell\AbstractCellRuleCombo;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;

final class Ruleset
{
    /** @var AbstractRule[] */
    private array  $rules;
    private string $columnNameId;

    /** @var int[] */
    private array $intputTypes = [];

    public function __construct(array $rules, string $columnNameId)
    {
        $this->rules = [];
        $this->columnNameId = $columnNameId;

        foreach ($rules as $ruleName => $options) {
            $rule = $this->ruleDiscovery((string)$ruleName, $options);
            if ($rule !== null) {
                $this->rules[$ruleName] = $rule;
                $this->intputTypes[] = $rule->getInputType();
            }
        }
    }

    /**
     * Validates a rule set against a cell value.
     * @param  array|string $cellValue        the value to validate
     * @param  int          $line             the line number of the value
     * @param  int          $linesToAggregate the number of lines to aggregate when outputting debug information
     * @return ErrorSuite   the suite of errors found during validation
     */
    public function validateRuleSet(array|string $cellValue, int $line, int $linesToAggregate = 0): ErrorSuite
    {
        $errors = new ErrorSuite();

        foreach ($this->rules as $rule) {
            if ($linesToAggregate > 0) {
                Utils::debug("  {$rule->getRuleCode()} - start");
            }

            $startTimer = \microtime(true);
            $errors->addError($rule->validate($cellValue, $line));

            if ($linesToAggregate > 0) {
                Utils::debugSpeed("  {$rule->getRuleCode()} -", $linesToAggregate, $startTimer);
            }
        }

        return $errors;
    }

    /**
     * Discover and return an instance of the appropriate rule class based on the given rule name.
     * @param  string                           $origRuleName the original rule name
     * @param  null|array|bool|float|int|string $options      the options for the rule
     * @return null|AbstractRule                an instance of the rule class or null if the rule is not found
     */
    public function ruleDiscovery(
        string $origRuleName,
        array|bool|float|int|string|null $options = null,
    ): ?AbstractRule {
        $mode = AbstractCellRuleCombo::parseMode($origRuleName);
        $noCombo = \preg_replace("/(_{$mode})\$/", '', $origRuleName);

        $origRuleClass = Utils::kebabToCamelCase($origRuleName);
        $comboRuleClass = Utils::kebabToCamelCase("combo_{$noCombo}");

        foreach (['Cell', 'Aggregate'] as $group) {
            foreach ([$origRuleClass, $comboRuleClass] as $ruleClass) {
                $posibleClassName = __NAMESPACE__ . "\\{$group}\\{$ruleClass}";

                $rule = $this->createRuleClassAttempt($posibleClassName, $options, $mode);
                if ($rule !== null) {
                    return $rule;
                }
            }
        }

        // throw new Exception("Rule \"{$origRuleName}\" not found."); // FIXME: replace to warning
        return null;
    }

    /**
     * Based on the fact that aggregation rules need different data types,
     * we choose the minimum possible and safe one to save RAM when preparing the array for aggregation.
     * Sometimes this can give us a benefit of up to 3 times.
     *
     * It also speeds up math functions a bit, but only if there are more than a million values.
     */
    public function getAggregationInputType(): int
    {
        if (\count($this->intputTypes) === 0) {
            return AbstractRule::INPUT_TYPE_UNDEF;
        }

        return \max($this->intputTypes);
    }

    /**
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function createRuleClassAttempt(
        string $posibleClassName,
        array|bool|float|int|string|null $options,
        string $mode,
    ): ?AbstractRule {
        if (\class_exists($posibleClassName)) {
            // @phpstan-ignore-next-line
            return new $posibleClassName($this->columnNameId, $options, $mode);
        }

        return null;
    }
}
