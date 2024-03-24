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

namespace JBZoo\CsvBlueprint\Csv;

use JBZoo\CsvBlueprint\Validators\ColumnValidator;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\Data\Data;

final class Column
{
    private const FALLBACK_VALUES = [
        'inherit'         => '',
        'name'            => '',
        'description'     => '',
        'type'            => 'base', // TODO: class
        'required'        => false,
        'allow_empty'     => false,
        'regex'           => null,
        'rules'           => [],
        'aggregate_rules' => [],
    ];

    private int   $id;
    private Data  $column;
    private array $rules;
    private array $aggRules;

    public function __construct(int $id, array $config)
    {
        $this->id       = $id;
        $this->column   = new Data($config);
        $this->rules    = $this->prepareRuleSet('rules');
        $this->aggRules = $this->prepareRuleSet('aggregate_rules');
    }

    public function getName(): string
    {
        return $this->column->getString('name', self::FALLBACK_VALUES['name']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->column->getString('description', self::FALLBACK_VALUES['description']);
    }

    public function getHumanName(): string
    {
        return $this->getId() . ':' . \trim($this->getName());
    }

    public function getKey(): string
    {
        if ($this->getName() !== '') {
            return $this->getName();
        }

        return (string)$this->getId();
    }

    public function isRequired(): bool
    {
        return $this->column->getBool('required', self::FALLBACK_VALUES['required']);
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getAggregateRules(): array
    {
        return $this->aggRules;
    }

    public function getInherit(): string
    {
        return $this->column->getString('inherit', self::FALLBACK_VALUES['inherit']);
    }

    public function validateCell(string $cellValue, int $line = Error::UNDEFINED_LINE): ErrorSuite
    {
        return (new ColumnValidator($this))->validateCell($cellValue, $line);
    }

    public function validateList(array &$cellValue): ErrorSuite
    {
        return (new ColumnValidator($this))->validateList($cellValue);
    }

    private function prepareRuleSet(string $schemaKey): array
    {
        $rules = [];

        $ruleSetConfig = $this->column->getSelf($schemaKey, [])->getArrayCopy();

        foreach ($ruleSetConfig as $ruleName => $ruleValue) {
            $rules[$ruleName] = $ruleValue;
        }

        return $rules;
    }
}
