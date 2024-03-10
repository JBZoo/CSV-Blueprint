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

use JBZoo\Data\Data;

final class ParseConfig
{
    public const ENCODING_UTF8    = 'utf-8';
    public const ENCODING_UTF16   = 'utf-16';
    public const ENCODING_UTF32   = 'utf-32';
    private const FALLBACK_VALUES = [
        'inherit'                => null,
        'bom'                    => false,
        'delimiter'              => ',',
        'quote_char'             => '\\',
        'enclosure'              => '"',
        'encoding'               => 'utf-8',
        'header'                 => true,
        'strict_column_order'    => false,
        'other_columns_possible' => false,
    ];

    private Data $structure;

    public function __construct(array $config)
    {
        $this->structure = new Data($config);
    }

    public function getInherit(): ?string
    {
        return $this->structure->getStringNull('inherit', self::FALLBACK_VALUES['inherit']);
    }

    public function isBom(): bool
    {
        return $this->structure->getBool('bom', self::FALLBACK_VALUES['bom']);
    }

    public function getDelimiter(): string
    {
        $value = $this->structure->getString('delimiter', self::FALLBACK_VALUES['delimiter']);
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Delimiter must be a single character');
    }

    public function getQuoteChar(): string
    {
        $value = $this->structure->getString('quote_char', self::FALLBACK_VALUES['quote_char']);
        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Quote char must be a single character');
    }

    public function getEnclosure(): string
    {
        $value = $this->structure->getString('enclosure', self::FALLBACK_VALUES['enclosure']);

        if (\strlen($value) === 1) {
            return $value;
        }

        throw new \InvalidArgumentException('Enclosure must be a single character');
    }

    public function getEncoding(): string
    {
        $encoding         = \strtolower(\trim($this->structure->getString('encoding', self::FALLBACK_VALUES['encoding'])));
        $availableOptions = [ // TODO: add flexible handler for this
            self::ENCODING_UTF8,
            self::ENCODING_UTF16,
            self::ENCODING_UTF32,
        ];

        $result = \in_array($encoding, $availableOptions, true) ? $encoding : null;
        if ($result) {
            return $result;
        }

        throw new \InvalidArgumentException("Invalid encoding: {$encoding}");
    }

    public function isHeader(): bool
    {
        return $this->structure->getBool('header', self::FALLBACK_VALUES['header']);
    }

    public function isStrictColumnOrder(): bool
    {
        return $this->structure->getBool('strict_column_order', self::FALLBACK_VALUES['strict_column_order']);
    }

    public function isOtherColumnsPossible(): bool
    {
        return $this->structure->getBool('other_columns_possible', self::FALLBACK_VALUES['other_columns_possible']);
    }

    public function getArrayCopy(): array
    {
        return [
            // System rules
            'inherit' => $this->getInherit(),
            // Reading rules
            'bom'        => $this->isBom(),
            'delimiter'  => $this->getDelimiter(),
            'quote_char' => $this->getQuoteChar(),
            'enclosure'  => $this->getEnclosure(),
            'encoding'   => $this->getEncoding(),
            'header'     => $this->isHeader(),
            // Global validation rules
            'strict_column_order'    => $this->isStrictColumnOrder(),
            'other_columns_possible' => $this->isOtherColumnsPossible(),
        ];
    }
}
