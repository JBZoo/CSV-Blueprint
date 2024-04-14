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
    private Data  $internalData;
    private array $rules;
    private array $aggRules;

    public function __construct(int $schemaId, array $config)
    {
        $this->schemaId = $schemaId;
        $this->internalData = new Data($config);
        $this->rules = $this->prepareRuleSet('rules');
        $this->aggRules = $this->prepareRuleSet('aggregate_rules');
    }

    /**
     * Retrieves the name from the internal data object.
     * @return string The name value retrieved from the internal data object. If the name
     *                does not exist, the method will return the fallback name value
     *                defined in the class constant FALLBACK_VALUES.
     */
    public function getName(): string
    {
        return $this->internalData->getString('name', self::FALLBACK_VALUES['name']);
    }

    /**
     * Returns the CSV offset.
     * @return null|int the CSV offset if set, or null if not set
     */
    public function getCsvOffset(): ?int
    {
        return $this->csvOffset;
    }

    /**
     * Returns the schema ID.
     * @return int the schema ID
     */
    public function getSchemaId(): int
    {
        return $this->schemaId;
    }

    /**
     * Gets the description from the internal data, or the fallback value if it is not set.
     * @return string the description value
     */
    public function getDescription(): string
    {
        return $this->internalData->getString('description', self::FALLBACK_VALUES['description']);
    }

    /**
     * Retrieves the human-friendly name of the object.
     * If the CSV offset is not null, the CSV offset is used as the prefix. Otherwise, the schema ID is used as the
     * prefix. The prefix is then concatenated with a colon (:) character and the trimmed name of the object.
     * @return string the human-friendly name of the object
     */
    public function getHumanName(): string
    {
        if ($this->csvOffset !== null) {
            $prefix = $this->csvOffset;
        } else {
            $prefix = $this->schemaId;
        }

        return $prefix . ':' . \trim($this->getName());
    }

    /**
     * Checks whether the object is required.
     * Retrieves the boolean value of 'required' from the internal data.
     * If the value is not found, it falls back to the default value defined in FALLBACK_VALUES array.
     * @return bool indicates whether the object is required
     */
    public function isRequired(): bool
    {
        return $this->internalData->getBool('required', self::FALLBACK_VALUES['required']);
    }

    /**
     * Retrieves the rules associated with the object.
     * The method simply returns the rules property of the object.
     * @return array the rules associated with the object
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Retrieves the aggregate rules associated with the object.
     * @return array the aggregate rules associated with the object
     */
    public function getAggregateRules(): array
    {
        return $this->aggRules;
    }

    /**
     * Retrieves the validator for the column.
     * Creates and returns a new instance of the ValidatorColumn class, initialized with the current object.
     * The ValidatorColumn class is responsible for validating the column based on certain rules and criteria.
     * @return ValidatorColumn the validator for the column
     */
    public function getValidator(): ValidatorColumn
    {
        return new ValidatorColumn($this);
    }

    /**
     * Validates a cell value using the validator associated with this object.
     * If the line number is not specified, it defaults to Error::UNDEFINED_LINE.
     * The cell value is passed to the underlying validator to perform the validation.
     * @param  string     $cellValue the value of the cell to validate
     * @param  int        $line      the line number where the cell is located
     * @return ErrorSuite the validation result as an ErrorSuite object
     */
    public function validateCell(string $cellValue, int $line = Error::UNDEFINED_LINE): ErrorSuite
    {
        return $this->getValidator()->validateCell($cellValue, $line);
    }

    /**
     * Sets the CSV offset.
     *
     * @param int $csvOffset the CSV offset to be set
     */
    public function setCsvOffset(int $csvOffset): void
    {
        $this->csvOffset = $csvOffset;
    }

    /**
     * Retrieves a clone of the internal Data object.
     * The method clones the internalData object and returns the clone to prevent modification of the internal data.
     * @return Data a cloned instance of the internal Data object
     */
    public function getData(): Data
    {
        return clone $this->internalData; // Clone to prevent modification of the internal data
    }

    private function prepareRuleSet(string $schemaKey): array
    {
        $rules = [];

        $ruleSetConfig = $this->internalData->getSelf($schemaKey, [])->getArrayCopy();
        foreach ($ruleSetConfig as $ruleName => $ruleValue) {
            $rules[$ruleName] = $ruleValue;
        }

        return $rules;
    }
}
