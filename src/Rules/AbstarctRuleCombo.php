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

abstract class AbstarctRuleCombo extends AbstarctRule
{
    protected const NAME = 'UNDEFINED';

    protected const VERBS = [
        self::EQ  => 'not equal',
        self::NOT => 'equal',
        self::MIN => 'less',
        self::MAX => 'greater',
    ];

    private string $comboRule;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $mode = self::DEFAULT,
    ) {
        parent::__construct($columnNameId, $options, $mode);

        $this->comboRule = \str_replace('_' . $this->mode, '', $this->ruleCode);
    }

    public static function parseMode(string $origRuleName): string
    {
        $postfixes = [self::MAX, self::MIN, self::NOT];

        if (\preg_match('/_(' . \implode('|', $postfixes) . ')$/', $origRuleName, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }
}
