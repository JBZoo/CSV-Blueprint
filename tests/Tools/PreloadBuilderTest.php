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

namespace JBZoo\PHPUnit\Tools;

use JBZoo\CsvBlueprint\Tools\PreloadBuilder;
use JBZoo\PHPUnit\TestCase;

use function JBZoo\PHPUnit\isContain;
use function JBZoo\PHPUnit\isNotContain;

class PreloadBuilderTest extends TestCase
{
    public function testPreloaderWithoutCompiling(): void
    {
        (new PreloadBuilder(enableOpcacheCompiler: false))
            ->setExcludes([
                __FILE__,
            ])
            ->setFiles(\get_included_files())
            ->saveToFile(PROJECT_BUILD . '/preload.php');

        self::assertFileExists(PROJECT_BUILD . '/preload.php');

        $content = \file_get_contents(PROJECT_BUILD . '/preload.php');
        isNotContain(__FILE__, $content);
        isContain('PreloadBuilder.php', $content);
        isContain('vendor/autoload.php', $content);

        isNotContain('function_exists', $content);
        isNotContain('opcache_compile_file', $content);
        isContain('require_once', $content);
    }

    public function testPreloaderWithCompiling(): void
    {
        (new PreloadBuilder(enableOpcacheCompiler: true))
            ->setExcludes([
                __FILE__,
            ])
            ->setFiles(\get_included_files())
            ->saveToFile(PROJECT_BUILD . '/preload.php');

        self::assertFileExists(PROJECT_BUILD . '/preload.php');

        $content = \file_get_contents(PROJECT_BUILD . '/preload.php');
        isNotContain(__FILE__, $content);
        isContain('PreloadBuilder.php', $content);
        isContain('vendor/autoload.php', $content);

        isContain('function_exists', $content);
        isContain('opcache_compile_file', $content);
        isNotContain('require_once', $content);
    }
}
