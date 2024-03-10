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

use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\CsvBlueprint\Validators\Validator;
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
    private array $aggregateRules;

    public function __construct(int $id, array $config)
    {
        $this->id             = $id;
        $this->column         = new Data($config);
        $this->rules          = $this->prepareRuleSet('rules');
        $this->aggregateRules = $this->prepareRuleSet('aggregate_rules');
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

    public function getType(): string
    {
        return $this->column->getString('type', self::FALLBACK_VALUES['type']);
    }

    public function isRequired(): bool
    {
        return $this->column->getBool('required', self::FALLBACK_VALUES['required']);
    }

    public function getRegex(): ?string
    {
        $regex = $this->column->getStringNull('regex', self::FALLBACK_VALUES['regex']);

        return Utils::prepareRegex($regex);
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getAggregateRules(): array
    {
        return $this->aggregateRules;
    }

    public function getInherit(): string
    {
        return $this->column->getString('inherit', self::FALLBACK_VALUES['inherit']);
    }

    public function validate(string $cellValue, int $line): ErrorSuite
    {
        return (new Validator($this))->validate($cellValue, $line);
    }

    private function prepareRuleSet(string $schemaKey): array
    {
        $rules = [];

        $ruleSetConfig = $this->column->getSelf($schemaKey, [])->getArrayCopy();

        foreach ($ruleSetConfig as $ruleName => $ruleValue) {
            if (\str_starts_with((string)$ruleName, 'custom_')) {
                $rules[$ruleName] = \array_merge(['class' => '', 'args' => []], $ruleValue);
            } else {
                $rules[$ruleName] = $ruleValue;
            }
        }

        return $rules;
    }
}
