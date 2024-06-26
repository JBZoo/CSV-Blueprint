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
    private array $includedFiles = [];
    private array $excludedFiles = [];

    public function __construct(
        private bool $enableOpcacheCompiler = false,
    ) {
    }

    /**
     * Saves the contents of the current object to a file.
     * @param string $filename the path of the file to save to
     * @param bool   $showInfo whether to display information about the included files
     */
    public function saveToFile(string $filename, bool $showInfo = false): void
    {
        $files = $this->buildFilelist();
        $lines = \array_merge($this->buildHeader(), $files);

        \file_put_contents($filename, \implode(\PHP_EOL, $lines) . \PHP_EOL);

        if ($showInfo) {
            Cli::out('Included files: ' . \count($files));
        }
    }

    /**
     * Sets the array of excluded files.
     * @param array $excludedFiles an array of excluded files
     */
    public function setExcludes(array $excludedFiles): self
    {
        $this->excludedFiles = $excludedFiles;
        return $this;
    }

    /**
     * Sets the files included in the object.
     * @param array $includedFiles an array of files to be included
     */
    public function setFiles(array $includedFiles): self
    {
        $this->includedFiles = $includedFiles;
        return $this;
    }

    private function buildFilelist(): array
    {
        $files = [];
        $fillList = \array_merge([
            \dirname(__DIR__, 2) . '/vendor/autoload.php',
        ], $this->includedFiles);

        foreach ($fillList as $path) {
            if ($this->isExcluded($path)) {
                continue;
            }

            $files[] = $this->enableOpcacheCompiler
                ? "\\opcache_compile_file('{$path}');"
                : "require_once '{$path}';";
        }

        return \array_unique($files);
    }

    private function isExcluded(string $path): bool
    {
        return \in_array($path, $this->excludedFiles, true);
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
