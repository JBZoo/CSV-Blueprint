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

use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\ValidatorColumn;
use JBZoo\Data\Data;

final class Column
{
    private const FALLBACK_VALUES = [
        'name'            => '',
        'description'     => '',
        'required'        => true,
        'rules'           => [],
        'aggregate_rules' => [],
    ];

    private ?int  $csvOffset = null;
    private int   $schemaId;
    private Data  $column;
    private array $rules;
    private array $aggRules;

    public function __construct(int $schemaId, array $config)
    {
        $this->schemaId = $schemaId;
        $this->column = new Data($config);
        $this->rules = $this->prepareRuleSet('rules');
        $this->aggRules = $this->prepareRuleSet('aggregate_rules');
    }

    public function getName(): string
    {
        return $this->column->getString('name', self::FALLBACK_VALUES['name']);
    }

    public function getCsvOffset(): int
    {
        if ($this->csvOffset === null) {
            throw new \RuntimeException('CSV offset is not defined');
        }

        return $this->csvOffset;
    }

    public function getSchemaId(): int
    {
        return $this->schemaId;
    }

    public function getDescription(): string
    {
        return $this->column->getString('description', self::FALLBACK_VALUES['description']);
    }

    public function getHumanName(): string
    {
        return $this->getSchemaId() . ':' . \trim($this->getName());
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

    public function getValidator(): ValidatorColumn
    {
        return new ValidatorColumn($this);
    }

    public function validateCell(string $cellValue, int $line = Error::UNDEFINED_LINE): ErrorSuite
    {
        return $this->getValidator()->validateCell($cellValue, $line);
    }

    public function setCsvOffset(int $csvOffset): void
    {
        $this->csvOffset = $csvOffset;
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
