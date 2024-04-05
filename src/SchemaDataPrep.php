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

namespace JBZoo\CsvBlueprint;

use JBZoo\Data\AbstractData;
use JBZoo\Data\Data;

final class SchemaDataPrep
{
    public const ALIAS_REGEX = '[a-z0-9-_]+';

    private const DEFAULTS = [
        'name'             => '',
        'description'      => '',
        'filename_pattern' => '',

        'inlcudes' => [],

        'csv' => [
            'inherit'    => null,
            'header'     => true,
            'delimiter'  => ',',
            'quote_char' => '\\',
            'enclosure'  => '"',
            'encoding'   => Schema::ENCODING_UTF8,
            'bom'        => false,
        ],

        'structural_rules' => [
            'strict_column_order' => true,
            'allow_extra_columns' => false,
        ],

        'column' => [
            'inherit'         => '',
            'name'            => '',
            'description'     => '',
            'example'         => null,
            'required'        => true,
            'rules'           => [],
            'aggregate_rules' => [],
        ],

        'rules'           => ['inherit' => ''],
        'aggregate_rules' => ['inherit' => ''],
    ];

    private AbstractData $data;
    private string       $basepath;

    /** @var Schema[] */
    private array $aliases;

    public function __construct(AbstractData $data, string $basepath)
    {
        $this->data = $data;
        $this->basepath = $basepath;
        $this->aliases = $this->prepareAliases($data);
    }

    public function buildData(): Data
    {
        $result = [
            'name'             => $this->buildName(),
            'description'      => $this->buildDescription(),
            'includes'         => $this->buildIncludes(),
            'filename_pattern' => $this->buildFilenamePattern(),
            'csv'              => $this->buildByKey('csv'),
            'structural_rules' => $this->buildByKey('structural_rules'),
            'columns'          => $this->buildColumns(),
        ];

        // Any extra keys to see schema validation errors
        foreach ($this->data->getArrayCopy() as $key => $value) {
            if (!isset($result[$key])) {
                $result[$key] = $value;
            }
        }

        return new Data($result);
    }

    public static function getAliasRegex(): string
    {
        return '/^' . self::ALIAS_REGEX . '$/i';
    }

    public static function validateAlias(string $alias): void
    {
        if ($alias === '') {
            throw new \InvalidArgumentException('Empty alias');
        }

        if (\preg_match(self::getAliasRegex(), $alias) === 0) {
            throw new \InvalidArgumentException("Invalid alias: \"{$alias}\"");
        }
    }

    private function parseAliasParts(string $inherit): array
    {
        $alias = null;
        $keyword = null;
        $columnName = null;
        $rules = null;

        $parts = \explode('/', $inherit);
        if (\count($parts) === 2) {
            [$alias, $keyword] = $parts;
        } elseif (\count($parts) === 3) {
            [$alias, $keyword, $columnName] = $parts;
        } elseif (\count($parts) === 4) {
            [$alias, $keyword, $columnName, $rules] = $parts;
        }

        return [
            'alias'   => $alias,
            'keyword' => $keyword,
            'column'  => $columnName,
            'rules'   => $rules,
        ];
    }

    /**
     * @return Schema[]
     */
    private function prepareAliases(AbstractData $data): array
    {
        $includes = [];

        foreach ($data->getArray('includes') as $alias => $includedPathOrArray) {
            self::validateAlias($alias);

            if (\is_array($includedPathOrArray)) {
                $includes[$alias] = new Schema($includedPathOrArray);
            } elseif (\file_exists($includedPathOrArray)) {
                $includes[$alias] = (new Schema($includedPathOrArray));
            } elseif (\file_exists("{$this->basepath}/{$includedPathOrArray}")) {
                $includes[$alias] = (new Schema("{$this->basepath}/{$includedPathOrArray}"));
            } else {
                throw new \InvalidArgumentException("Unknown included file: \"{$includedPathOrArray}\"");
            }
        }

        return $includes;
    }

    private function getParentSchema(string $alias): Schema
    {
        if (isset($this->aliases[$alias])) {
            return $this->aliases[$alias];
        }

        throw new \InvalidArgumentException("Unknown included alias: \"{$alias}\"");
    }

    private function buildFilenamePattern(): string
    {
        $inherit = $this->data->findString('filename_pattern.inherit');

        if (\str_ends_with($inherit, '/filename_pattern')) {
            $inheritParts = $this->parseAliasParts($inherit);
            $parent = $this->getParentSchema($inheritParts['alias']);
            return $parent->getData()->get('filename_pattern');
        }

        return $this->data->getString('filename_pattern', self::DEFAULTS['filename_pattern']);
    }

    private function buildByKey(string $key = 'structural_rules'): array
    {
        $inherit = $this->data->findString("{$key}.inherit");

        $parentConfig = [];
        if (\preg_match('/' . self::ALIAS_REGEX . '\/' . $key . '$/i', $inherit) === 1) {
            $inheritParts = $this->parseAliasParts($inherit);
            $parent = $this->getParentSchema($inheritParts['alias']);
            $parentConfig = $parent->getData()->getArray($key);
        }

        $result = Utils::mergeConfigs(self::DEFAULTS[$key], $parentConfig, $this->data->getArray($key));
        unset($result['inherit']);

        return $result;
    }

    private function buildColumns(): array
    {
        $columns = [];

        foreach ($this->data->getArray('columns') as $columnIndex => $column) {
            $columnData = new Data($column);
            $columnInherit = $columnData->getString('inherit');

            $parentConfig = [];
            if (\preg_match('/' . self::ALIAS_REGEX . '\/columns\/[^\/]+$/i', $columnInherit) === 1) {
                $inheritParts = $this->parseAliasParts($columnInherit);
                $parent = $this->getParentSchema($inheritParts['alias']);
                $parentColumn = $parent->getColumn($inheritParts['column']);
                if ($parentColumn === null) {
                    throw new \InvalidArgumentException("Unknown column: \"{$inheritParts['column']}\"");
                }

                $parentConfig = $parentColumn->getData()->getArrayCopy();
            }

            $actualColumn = Utils::mergeConfigs(self::DEFAULTS['column'], $parentConfig, $columnData->getArrayCopy());
            $actualColumn['rules'] = $this->buildRules($actualColumn['rules'], 'rules');
            $actualColumn['aggregate_rules'] = $this->buildRules($actualColumn['aggregate_rules'], 'aggregate_rules');

            unset($actualColumn['inherit']);

            $columns[$columnIndex] = $actualColumn;
        }

        return $columns;
    }

    private function buildIncludes(): array
    {
        $result = [];
        foreach ($this->aliases as $alias => $schema) {
            $result[$alias] = $schema->getFilename();
        }

        return $result;
    }

    private function buildName(): string
    {
        return $this->data->getString('name', self::DEFAULTS['name']);
    }

    private function buildDescription(): string
    {
        return $this->data->getString('description', self::DEFAULTS['description']);
    }

    private function buildRules(array $rules, string $typeOfRules): array
    {
        $inherit = $rules['inherit'] ?? '';

        $parentConfig = [];
        if (\preg_match('/' . self::ALIAS_REGEX . '\/columns\/[^\/]+\/' . $typeOfRules . '$/i', $inherit) === 1) {
            $inheritParts = $this->parseAliasParts($inherit);
            $parent = $this->getParentSchema($inheritParts['alias']);
            $parentColumn = $parent->getColumn($inheritParts['column']);
            if ($parentColumn === null) {
                throw new \InvalidArgumentException("Unknown column: \"{$inheritParts['column']}\"");
            }

            $parentConfig = $parentColumn->getData()->getArray($typeOfRules);
        }

        $actualRules = Utils::mergeConfigs(self::DEFAULTS[$typeOfRules], $parentConfig, $rules);
        unset($actualRules['inherit']);

        return $actualRules;
    }
}
