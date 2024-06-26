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
use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\Data\AbstractData;

use function JBZoo\Data\data;
use function JBZoo\Data\phpArray;

final class ValidatorSchema
{
    private ?string      $filename;
    private bool         $isHeader;
    private AbstractData $data;

    public function __construct(Schema $schema)
    {
        $this->filename = $schema->getFilename();
        $this->data = $schema->getData();
        $this->isHeader = $schema->csvHasHeader();
    }

    public function validate(bool $quickStop = false): ErrorSuite
    {
        [$expectedMeta, $expectedColumn] = self::getExpected();
        [$actualMeta, $actualColumns] = $this->getActual();

        $allErrors = new ErrorSuite($this->filename);

        $errors = self::validateMeta($expectedMeta, $actualMeta, $quickStop);
        if ($errors->count() > 0) {
            $allErrors->addErrorSuit($errors);
            if ($quickStop) {
                return $allErrors;
            }
        }

        unset($expectedColumn['extra']);
        $errors = $this->validateColumns($expectedColumn, $actualColumns, $quickStop);
        if ($errors->count() > 0) {
            $allErrors->addErrorSuit($errors);
            if ($quickStop) {
                return $allErrors;
            }
        }

        return $allErrors;
    }

    /**
     * Retrieve the expected meta and column structure.
     * @return array     the expected meta and column data
     * @throws Exception if the reference schema file is not found
     */
    public static function getExpected(): array
    {
        $referenceFile = \dirname(__DIR__, 2) . '/schema-examples/full.php';
        if (!\file_exists($referenceFile)) {
            throw new Exception("Reference schema not found: {$referenceFile}");
        }

        $expected = phpArray($referenceFile);
        $expectedColumn = $expected->findArray('columns.0');
        $expectedMeta = $expected->remove('columns')->getArrayCopy();

        return [$expectedMeta, $expectedColumn];
    }

    private function getActual(): array
    {
        $actualColumns = $this->data->findSelf('columns')->getArrayCopy();
        $actualMeta = $this->data->remove('columns');
        unset($actualMeta['extra']);

        foreach (\array_keys($actualColumns) as $index) {
            unset($actualColumns[$index]['extra']);
        }

        return [$actualMeta, data($actualColumns)];
    }

    private function validateColumns(
        array $expectedColumn,
        AbstractData $actualColumns,
        bool $quickStop = false,
    ): ErrorSuite {
        $errors = new ErrorSuite();

        foreach ($actualColumns->getArrayCopy() as $columnKey => $actualColumn) {
            $columnId = "{$columnKey}:" . ($actualColumn['name'] ?? '');

            // Validate column names
            $errors->addErrorSuit($this->validateColumn($actualColumn, $columnId, (int)$columnKey));
            if ($quickStop && $errors->count() > 0) {
                return $errors;
            }

            // Validate column schema
            $columnErrors = Utils::compareArray(
                $expectedColumn,
                $actualColumn,
                $columnId,
                "columns.{$columnKey}",
            );

            foreach ($columnErrors as $columnError) {
                $errors->addError(new Error('schema', $columnError[1], $columnError[0]));
                if ($quickStop && $errors->count() > 0) {
                    return $errors;
                }
            }
        }

        return $errors;
    }

    private function validateColumn(array $actualColumn, string $columnId, int $columnKey): ErrorSuite
    {
        return (new ErrorSuite())
            ->addError($this->validateColumnName($actualColumn, $columnId))
            ->addErrorSuit(self::validateColumnExample($actualColumn, $columnKey));
    }

    private function validateColumnName(array $actualColumn, string $columnId): ?Error
    {
        if ($this->isHeader && (!isset($actualColumn['name']) || $actualColumn['name'] === '')) {
            return new Error(
                'schema',
                'The key "<c>name</c>" must be non-empty because the option "<green>csv.header</green>" = true',
                $columnId,
            );
        }

        return null;
    }

    private static function validateColumnExample(array $actualColumn, int $schemaColumnId): ?ErrorSuite
    {
        $exclude = [
            'Some example', // I.e. this value is taken from full.yml, then it will be invalid in advance.
            null,
        ];

        if (isset($actualColumn['example']) && !\in_array($actualColumn['example'], $exclude, true)) {
            return (new Column($schemaColumnId, $actualColumn))->validateCell((string)$actualColumn['example']);
        }

        return null;
    }

    private static function validateMeta(
        array $expectedMeta,
        AbstractData $actualMeta,
        bool $quickStop = false,
    ): ErrorSuite {
        $errors = new ErrorSuite();

        $actualMetaAsArray = $actualMeta->getArrayCopy();
        $actualPresets = $actualMetaAsArray['presets'] ?? [];
        unset(
            $expectedMeta['presets'],
            $expectedMeta['extra'],
            $actualMetaAsArray['presets'],
            $actualMetaAsArray['extra'],
        );

        $metaErrors = Utils::compareArray($expectedMeta, $actualMetaAsArray, 'meta', '.');

        foreach ($actualPresets as $alias => $includedFile) {
            if ($alias === '') {
                $errors->addError(new Error('presets', 'Defined alias is empty'));
            }

            if (!\is_string($includedFile)) {
                $errors->addError(new Error('presets', 'Included filepath must be a string'));
            }
        }

        foreach ($metaErrors as $metaError) {
            $errors->addError(new Error('schema', $metaError[1], $metaError[0]));
            if ($quickStop) {
                return $errors;
            }
        }

        return $errors;
    }
}
