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
    /** @var AbstarctRule[] */
    private array  $rules;
    private string $columnNameId;

    public function __construct(array $rules, string $columnNameId)
    {
        $this->rules        = [];
        $this->columnNameId = $columnNameId;

        foreach ($rules as $ruleName => $options) {
            $rule = $this->ruleDiscovery((string)$ruleName, $options);
            if ($rule !== null) {
                $this->rules[$ruleName] = $rule;
            }
        }
    }

    public function validateRuleSet(array|string &$cellValue, int $line): ErrorSuite
    {
        $errors = new ErrorSuite();

        foreach ($this->rules as $rule) {
            Utils::debug("Start validate rule: {$rule->getRuleCode()}");
            $errors->addError($rule->validate($cellValue, $line));
            Utils::debug('End');
        }

        return $errors;
    }

    public function ruleDiscovery(
        string $origRuleName,
        null|array|bool|float|int|string $options = null,
    ): ?AbstarctRule {
        $mode    = AbstractCellRuleCombo::parseMode($origRuleName);
        $noCombo = \preg_replace("/(_{$mode})\$/", '', $origRuleName);

        $origRuleClass  = Utils::kebabToCamelCase($origRuleName);
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
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function createRuleClassAttempt(
        string $posibleClassName,
        null|array|bool|float|int|string $options,
        string $mode,
    ): ?AbstarctRule {
        if (\class_exists($posibleClassName)) {
            // @phpstan-ignore-next-line
            return new $posibleClassName($this->columnNameId, $options, $mode);
        }

        return null;
    }
}
