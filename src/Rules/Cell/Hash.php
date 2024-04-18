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

namespace JBZoo\CsvBlueprint\Rules\Cell;

final class Hash extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            self::getHelpTitle(),
            [self::DEFAULT => ['set_algo', 'Example: "1234567890abcdef".']],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if ($cellValue === '') {
            return null;
        }

        $regex = self::getRegexList();
        $hashAlg = $this->getOptionAsString();

        if (!isset($regex[$hashAlg])) {
            return "The algorithm \"{$hashAlg}\" is not supported.";
        }

        if (\preg_match($regex[$hashAlg], $cellValue) === 0) {
            return "The value \"<c>{$cellValue}</c>\" is not a valid hash for the " .
                "algorithm \"<green>{$hashAlg}</green>\"";
        }

        return null;
    }

    public static function analyzeColumnValues(array $columnValues): array|bool|string
    {
        $regex = self::getRegexList();

        $countByRegex = [];

        foreach ($columnValues as $value) {
            foreach ($regex as $alg => $pattern) {
                if (\preg_match($pattern, $value) === 1) {
                    $countByRegex[$alg] = ($countByRegex[$alg] ?? 0) + 1;
                }
            }
        }

        $originalCount = \count(\array_filter($columnValues, static fn (string $value) => $value !== ''));
        $validHashAlg = \array_keys(\array_filter($countByRegex, static fn (int $count) => $count === $originalCount));

        if (\count($validHashAlg) > 0) {
            return (string)\reset($validHashAlg);
        }

        return false;
    }

    private static function getRegex(int $length, string $charset = '[a-f0-9]'): string
    {
        return "/^{$charset}{{$length}}\$/i";
    }

    private static function getRegexList(): array
    {
        return [
            'md5' => self::getRegex(32),
            'md4' => self::getRegex(32),
            'md2' => self::getRegex(32),

            'sha1'       => self::getRegex(40),
            'sha224'     => self::getRegex(56),
            'sha256'     => self::getRegex(64),
            'sha384'     => self::getRegex(96),
            'sha512/224' => self::getRegex(56),
            'sha512/256' => self::getRegex(64),
            'sha512'     => self::getRegex(128),
            'sha3-224'   => self::getRegex(56),
            'sha3-256'   => self::getRegex(64),
            'sha3-384'   => self::getRegex(96),
            'sha3-512'   => self::getRegex(128),

            'ripemd128' => self::getRegex(32),
            'ripemd160' => self::getRegex(40),
            'ripemd256' => self::getRegex(64),
            'ripemd320' => self::getRegex(80),

            'whirlpool' => self::getRegex(128),

            'tiger128,3' => self::getRegex(32),
            'tiger160,3' => self::getRegex(40),
            'tiger192,3' => self::getRegex(48),
            'tiger128,4' => self::getRegex(32),
            'tiger160,4' => self::getRegex(40),
            'tiger192,4' => self::getRegex(48),

            'snefru'    => self::getRegex(64),
            'snefru256' => self::getRegex(64),

            'gost'        => self::getRegex(64),
            'gost-crypto' => self::getRegex(64),

            'crc32'  => self::getRegex(8),
            'crc32b' => self::getRegex(8),
            'crc32c' => self::getRegex(8),

            'adler32' => self::getRegex(8),

            'fnv132'  => self::getRegex(8),
            'fnv1a32' => self::getRegex(8),

            'fnv164'  => self::getRegex(16),
            'fnv1a64' => self::getRegex(16),

            'joaat' => self::getRegex(8),

            'murmur3a' => self::getRegex(8),
            'murmur3c' => self::getRegex(32),
            'murmur3f' => self::getRegex(32),

            'xxh32'  => self::getRegex(8),
            'xxh64'  => self::getRegex(16),
            'xxh3'   => self::getRegex(16),
            'xxh128' => self::getRegex(32),

            'haval128,3' => self::getRegex(32),
            'haval160,3' => self::getRegex(40),
            'haval192,3' => self::getRegex(48),
            'haval224,3' => self::getRegex(56),
            'haval256,3' => self::getRegex(64),
            'haval128,4' => self::getRegex(32),
            'haval160,4' => self::getRegex(40),
            'haval192,4' => self::getRegex(48),
            'haval224,4' => self::getRegex(56),
            'haval256,4' => self::getRegex(64),
            'haval128,5' => self::getRegex(32),
            'haval160,5' => self::getRegex(40),
            'haval192,5' => self::getRegex(48),
            'haval224,5' => self::getRegex(56),
            'haval256,5' => self::getRegex(64),
        ];
    }

    private static function getHelpTitle(): array
    {
        $maxOnLine = 10;
        $lines = \array_chunk(\array_keys(self::getRegexList()), $maxOnLine);

        $result = ['Check if the value is a valid hash. Supported algorithms:'];
        foreach ($lines as $line) {
            $result[] = ' - ' . \implode(', ', $line);
        }

        return $result;
    }
}
