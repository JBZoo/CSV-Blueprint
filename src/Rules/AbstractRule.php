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

use JBZoo\CsvBlueprint\Rules\Cell\Exception;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ValidatorColumn;

use function JBZoo\Utils\bool;

abstract class AbstractRule
{
    public const INPUT_TYPE = self::INPUT_TYPE_UNDEF;

    public const INPUT_TYPE_COUNTER = 0;
    public const INPUT_TYPE_INTS = 1;
    public const INPUT_TYPE_FLOATS = 2;
    public const INPUT_TYPE_STRINGS = 3;
    public const INPUT_TYPE_UNDEF = 4;

    // Modes
    public const DEFAULT = 'default';
    public const EQ = '';
    public const NOT = 'not';
    public const MIN = 'min';
    public const MAX = 'max';
    public const LESS = 'less';
    public const GREATER = 'greater';

    protected string $columnNameId;
    protected string $ruleCode;
    protected string $mode;

    private null|array|bool|float|int|string $options;

    abstract public function getHelpMeta(): array;

    public function __construct(
        string $columnNameId,
        null|array|bool|float|int|string $options,
        string $mode = self::DEFAULT,
    ) {
        $this->mode = $mode;
        $this->columnNameId = $columnNameId;
        $this->ruleCode = $this->getRuleCode();
        $this->options = $options;
        // TODO: Move resolving and validating expected value on this stage to make it only once (before validation).
    }

    /**
     * Validate the given cell value based on the rule defined in the subclass.
     * @param  array|string $cellValue the value of the cell to validate
     * @param  int          $line      the line number of the cell
     * @return ?Error       An Error object if validation fails, null otherwise
     * @throws Exception    If the "validateRule" method is not found in the subclass
     */
    public function validate(array|string $cellValue, int $line = ValidatorColumn::FALLBACK_LINE): ?Error
    {
        // TODO: Extract to abstract boolean cell/agregate rule
        if ($this->isEnabled($cellValue) === false) {
            return null;
        }

        if (\method_exists($this, 'validateRule')) { // TODO: Need to be removed
            try {
                /** @phan-suppress-next-line PhanUndeclaredMethod */
                $error = $this->validateRule($cellValue);
                if ($error !== null) {
                    return new Error($this->ruleCode, $error, $this->columnNameId, $line);
                }
            } catch (\Exception $e) {
                return new Error(
                    $this->ruleCode,
                    "Unexpected error: {$e->getMessage()}",
                    $this->columnNameId,
                    $line,
                );
            }
        } else {
            throw new Exception('Method "validateRule" not found in ' . static::class);
        }

        return null;
    }

    /**
     * Get the help documentation for the current instance.
     * @return string The help documentation
     */
    public function getHelp(): string
    {
        return (new DocBuilder($this))->getHelp();
    }

    /**
     * Retrieves the rule code based on the provided mode.
     * @param  null|string $mode the mode for the rule code
     * @return string      the generated rule code
     */
    public function getRuleCode(?string $mode = null): string
    {
        $mode ??= $this->mode;
        $postfix = $mode !== self::EQ && $mode !== self::DEFAULT ? "_{$mode}" : '';

        return Utils::camelToKebabCase((new \ReflectionClass($this))->getShortName()) . $postfix;
    }

    /**
     * Retrieves the input type. It uses for memmory optimization.
     * @return int the input type
     * @phan-suppress PhanPluginPossiblyStaticPublicMethod
     */
    public function getInputType(): int
    {
        return static::INPUT_TYPE;
    }

    /**
     * Test if all values in the given column pass the testValue check.
     * @param  array             $columnValues the values of the column to test
     * @return array|bool|string true if all values pass the testValue check, false otherwise
     */
    public static function analyzeColumnValues(array $columnValues): array|bool|string
    {
        foreach ($columnValues as $cellValue) {
            if ($cellValue === '') {
                continue;
            }

            if (!static::testValue($cellValue)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the given cell value is valid.
     * @param  string    $cellValue the value to test
     * @return bool      true if the cell value is valid, false otherwise
     * @throws Exception when the method is not implemented in the child class
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phan-suppress PhanUnusedPublicMethodParameter
     */
    public static function testValue(string $cellValue): bool
    {
        throw new Exception('Not implemented yet. Please override this method in the child class.');
    }

    /**
     * Checks if the rule is enabled based on the value of the options property.
     * Converts the options property to a boolean value and throws an exception if it is not a boolean.
     * @return bool      true if the rule is enabled, false otherwise
     * @throws Exception if the options property is not a boolean
     */
    protected function isEnabledByOption(): bool
    {
        // TODO: Replace to warning message
        if (!\is_bool($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be true|false.',
            );
        }

        return bool($this->options);
    }

    /**
     * Converts the option to a string representation.
     * @return string     the option as a string representation
     * @throws \Exception If the option is an array, indicating an invalid option. The exception message includes the
     *                    invalid options list and the rule code.
     */
    protected function getOptionAsString(): string
    {
        // TODO: Replace to warning message
        if (\is_array($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be int/float/string.',
            );
        }

        return (string)$this->options;
    }

    /**
     * Converts the option to an integer representation.
     * @return int        the option as an integer representation
     * @throws \Exception If the option is an empty string or not a numeric value, indicating an invalid option. The
     *                    exception message includes the invalid option value and the rule code.
     */
    protected function getOptionAsInt(): int
    {
        // TODO: Replace to warning message
        if ($this->options === '' || !\is_numeric($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be integer.',
            );
        }

        return (int)$this->options;
    }

    /**
     * Converts the option to a float representation.
     * @return float      the option as a float representation
     * @throws \Exception If the option is an empty string or not numeric, indicating an invalid option. The exception
     *                    message includes the invalid option and the rule code.
     */
    protected function getOptionAsFloat(): float
    {
        // TODO: Replace to warning message
        if ($this->options === '' || !\is_numeric($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be integer/float.',
            );
        }

        return (float)$this->options;
    }

    /**
     * Converts the options into an array.
     * Checks if the options property is an array and throws an exception if it is not.
     * The exception includes the invalid option and the rule code.
     * The options property should be an array of strings.
     * @return array     the options as an array
     * @throws Exception if the options property is not an array
     */
    protected function getOptionAsArray(): array
    {
        // TODO: Replace to warning message
        if (!\is_array($this->options)) {
            $options = Utils::printList($this->options);
            throw new Exception(
                "Invalid option {$options} for the \"{$this->getRuleCode()}\" rule. " .
                'It should be array of strings.',
            );
        }

        return $this->options;
    }

    /**
     * Optimize performance
     * There is no need to validate empty values for predicates or if it's disabled.
     */
    protected function isEnabled(array|string $cellValue): bool
    {
        // List of rules that should be checked for empty values
        $exclusions = [
            'not_empty',
            'exact_value',
            'length_min',
        ];

        if (
            (\str_starts_with($this->ruleCode, 'is_') || \str_starts_with($this->ruleCode, 'ag:is_'))
            && !$this->isEnabledByOption()
        ) {
            return false;
        }

        if (\in_array($this->ruleCode, $exclusions, true)) {
            return true;
        }

        return $cellValue !== '';
    }
}
