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

namespace JBZoo\CsvBlueprint\Tools;

use JBZoo\Utils\Cli;

final class PreloadBuilder
{
    private array $files = [];
    private array $excludes = [];

    public function __construct(
        private bool $enableOpcacheCompiler = false,
    ) {
    }

    public function saveToFile(string $filename, bool $showInfo = false): void
    {
        $files = $this->buildFilelist();
        $lines = \array_merge($this->buildHeader(), $files);

        \file_put_contents($filename, \implode(\PHP_EOL, $lines) . \PHP_EOL);

        if ($showInfo) {
            Cli::out('Included files: ' . \count($files));
        }
    }

    public function setExcludes(array $excludes): self
    {
        $this->excludes = $excludes;
        return $this;
    }

    public function setFiles(array $files): self
    {
        $this->files = $files;
        return $this;
    }

    private function buildFilelist(): array
    {
        $files = [];
        $fillList = \array_merge([
            \dirname(__DIR__, 2) . '/vendor/autoload.php',
        ], $this->files);

        foreach ($fillList as $path) {
            if ($this->isExcluded($path)) {
                continue;
            }

            $files[] = $this->enableOpcacheCompiler
                ? "\\opcache_compile_file('{$path}');"
                : "require_once '{$path}';";
        }

        return $files;
    }

    private function isExcluded(string $path): bool
    {
        return \in_array($path, $this->excludes, true);
    }

    private function buildHeader(): array
    {
        $header = [
            '<?php declare(strict_types=1);',
            '',
        ];

        if ($this->enableOpcacheCompiler) {
            $header = \array_merge($header, [
                "if (!\\function_exists('opcache_compile_file') ||",
                "    !\\ini_get('opcache.enable') ||",
                "    !\\ini_get('opcache.enable_cli')",
                ') {',
                "    echo 'Opcache is not available.';",
                '    die(1);',
                '}',
                '',
            ]);
        }

        return $header;
    }
}
