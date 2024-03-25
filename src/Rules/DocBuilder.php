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

use JBZoo\CsvBlueprint\Rules\AbstarctRule as Rule;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCount;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountEmpty;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountNotEmpty;
use JBZoo\CsvBlueprint\Rules\Cell\ComboLength;
use JBZoo\CsvBlueprint\Rules\Cell\ComboPrecision;
use JBZoo\CsvBlueprint\Rules\Cell\ComboWordCount;

final class DocBuilder
{
    private const HELP_FALLBACK = [
        Rule::DEFAULT => ['FIXME', 'Add description.'],
    ];

    private const HELP_COMBO_INT = [
        Rule::MIN     => ['1', 'x >= 1'],
        Rule::GREATER => ['2', 'x >  2'],
        Rule::NOT     => ['0', 'x != 0'],
        Rule::EQ      => ['7', 'x == 7'],
        Rule::LESS    => ['8', 'x <  8'],
        Rule::MAX     => ['9', 'x <= 9'],
    ];

    private const HELP_COMBO_FLOAT = [
        Rule::MIN     => ['1.0', 'x >= 1.0'],
        Rule::GREATER => ['2.0', 'x >  2.0'],
        Rule::NOT     => ['5.0', 'x != 5.0'],
        Rule::EQ      => ['7.0', 'x == 7.0'],
        Rule::LESS    => ['8.0', 'x <  8.0'],
        Rule::MAX     => ['9.0', 'x <= 9.0'],
    ];

    private const RULES_ACCEPT_ONLY_INT = [
        ComboLength::class,
        ComboWordCount::class,
        ComboPrecision::class,
        ComboCount::class,
        ComboCountEmpty::class,
        ComboCountNotEmpty::class,
    ];

    private const HELP_LEFT_PAD = 6;
    private const HELP_DESC_PAD = 40;

    private array  $topHelp;
    private array  $options;
    private Rule   $rule;
    private string $ymlRuleCode;
    private string $ymlRuleCodeClean;

    public function __construct(Rule $rule)
    {
        $this->rule = $rule;

        [$this->topHelp, $this->options] = $rule->getMeta();
        if (\count($this->options) === 0) {
            $this->options = \in_array(\get_class($rule), self::RULES_ACCEPT_ONLY_INT, true)
                ? self::HELP_COMBO_INT
                : self::HELP_COMBO_FLOAT;
        }

        $this->ymlRuleCode      = $this->getYmlRuleCode($rule->getRuleCode());
        $this->ymlRuleCodeClean = $this->getYmlRuleCodeClean($rule->getRuleCode());
    }

    public function getHelp(): string
    {
        $leftPad = \str_repeat(' ', self::HELP_LEFT_PAD);

        $topComment = '';
        if (\count($this->topHelp) > 0) {
            $topComment = "{$leftPad}# " . \implode("\n{$leftPad}# ", $this->topHelp);
        }

        if ($this->rule instanceof AbstarctRuleCombo) {
            return \implode("\n", [
                $topComment,
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::MIN], Rule::MIN),
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::GREATER], Rule::GREATER),
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::NOT], Rule::NOT),
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::EQ], Rule::EQ),
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::LESS], Rule::LESS),
                $this->renderLine($this->ymlRuleCode, $this->options[Rule::MAX], Rule::MAX),
            ]);
        }

        return \implode("\n", [
            $topComment,
            $this->renderLine($this->ymlRuleCodeClean, $this->options[Rule::DEFAULT], Rule::EQ),
        ]);
    }

    private function renderLine(string $ruleCode, array $row, string $mode): string
    {
        $leftPad = \str_repeat(' ', self::HELP_LEFT_PAD);

        $baseKeyVal = $mode === ''
            ? "{$leftPad}{$ruleCode}: {$row[0]}"
            : "{$leftPad}{$ruleCode}_{$mode}: {$row[0]}";

        if (isset($row[1]) && $row[1] !== '') {
            return \str_pad($baseKeyVal, self::HELP_DESC_PAD, ' ', \STR_PAD_RIGHT) . "# {$row[1]}";
        }

        return $baseKeyVal;
    }

    private function getYmlRuleCode(string $origRuleName): string
    {
        $postfixes = [
            Rule::MIN,
            Rule::GREATER,
            Rule::NOT,
            Rule::LESS,
            Rule::MAX,
        ];

        $result = $this->getYmlRuleCodeClean($origRuleName);

        if (\preg_match('/(.*?)_(' . \implode('|', $postfixes) . ')$/', $result, $matches) === 1) {
            return $matches[1];
        }

        return $result;
    }

    private function getYmlRuleCodeClean(string $origRuleCode): string
    {
        return \str_replace('ag:', '', $origRuleCode);
    }
}
