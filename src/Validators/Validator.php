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

final class Validator
{
    private Column  $column;
    private Ruleset $ruleset;

    public function __construct(Column $column)
    {
        $this->ruleset = new Ruleset($column->getRules(), $column->getHumanName());
    }

    public function validate(?string $cellValue, int $line): array
    {
        return $this->ruleset->validate($cellValue, $line);
    }
}
