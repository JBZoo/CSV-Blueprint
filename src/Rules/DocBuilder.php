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
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountDistinct;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountEmpty;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountEven;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountNegative;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountNotEmpty;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountOdd;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountPositive;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountPrime;
use JBZoo\CsvBlueprint\Rules\Aggregate\ComboCountZero;
use JBZoo\CsvBlueprint\Rules\Cell\ComboLength;
use JBZoo\CsvBlueprint\Rules\Cell\ComboPrecision;
use JBZoo\CsvBlueprint\Rules\Cell\ComboWordCount;

final class DocBuilder
{
    private const HELP_COMBO_INT = [
        Rule::DEFAULT => ['FIXME', 'Add description.'],
        Rule::MIN     => ['1', 'x >= 1'],
        Rule::GREATER => ['2', 'x >  2'],
        Rule::NOT     => ['0', 'x != 0'],
        Rule::EQ      => ['7', 'x == 7'],
        Rule::LESS    => ['8', 'x <  8'],
        Rule::MAX     => ['9', 'x <= 9'],
    ];

    private const HELP_COMBO_FLOAT = [
        Rule::DEFAULT => ['FIXME', 'Add description.'],
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
        ComboCountDistinct::class,
        ComboCountPositive::class,
        ComboCountNegative::class,
        ComboCountZero::class,
        ComboCountEven::class,
        ComboCountOdd::class,
        ComboCountPrime::class,
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

        [$this->topHelp, $this->options] = $rule->getHelpMeta();
        if (\count($this->options) === 0) {
            $this->options = \in_array(\get_class($rule), self::RULES_ACCEPT_ONLY_INT, true)
                ? self::HELP_COMBO_INT
                : self::HELP_COMBO_FLOAT;
        }

        $this->ymlRuleCode      = self::getYmlRuleCode($rule->getRuleCode());
        $this->ymlRuleCodeClean = self::getYmlRuleCodeClean($rule->getRuleCode());
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
                self::renderLine($this->ymlRuleCode, $this->options[Rule::MIN], Rule::MIN),
                self::renderLine($this->ymlRuleCode, $this->options[Rule::GREATER], Rule::GREATER),
                self::renderLine($this->ymlRuleCode, $this->options[Rule::NOT], Rule::NOT),
                self::renderLine($this->ymlRuleCode, $this->options[Rule::EQ], Rule::EQ),
                self::renderLine($this->ymlRuleCode, $this->options[Rule::LESS], Rule::LESS),
                self::renderLine($this->ymlRuleCode, $this->options[Rule::MAX], Rule::MAX),
            ]);
        }

        return \implode("\n", [
            $topComment,
            self::renderLine($this->ymlRuleCodeClean, $this->options[Rule::DEFAULT], Rule::EQ),
        ]);
    }

    private static function getYmlRuleCode(string $origRuleName): string
    {
        $postfixes = [Rule::MIN, Rule::GREATER, Rule::NOT, Rule::LESS, Rule::MAX];

        $result = self::getYmlRuleCodeClean($origRuleName);

        if (\preg_match('/(.*?)_(' . \implode('|', $postfixes) . ')$/', $result, $matches) === 1) {
            return $matches[1];
        }

        return $result;
    }

    private static function getYmlRuleCodeClean(string $origRuleCode): string
    {
        return \str_replace('ag:', '', $origRuleCode);
    }

    private static function renderLine(string $ruleCode, array $row, string $mode): string
    {
        $leftPad = \str_repeat(' ', self::HELP_LEFT_PAD);
        $descPad = self::HELP_DESC_PAD;

        $baseKeyVal = $mode === ''
            ? "{$leftPad}{$ruleCode}: {$row[0]}"
            : "{$leftPad}{$ruleCode}_{$mode}: {$row[0]}";

        if (\strlen($baseKeyVal) > $descPad) {
            $descPad = 60;
        }

        if (isset($row[1]) && $row[1] !== '') {
            return \str_pad($baseKeyVal, $descPad, ' ', \STR_PAD_RIGHT) . "# {$row[1]}";
        }

        return $baseKeyVal;
    }
}
