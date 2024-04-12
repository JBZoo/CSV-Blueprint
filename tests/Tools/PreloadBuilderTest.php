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

use function JBZoo\PHPUnit\isNotContain;

class PreloadBuilderTest extends TestCase
{
    public function testPreloader(): void
    {
        (new PreloadBuilder(isCompiler: false))
            ->setExcludes([
                __FILE__,
            ])
            ->setFiles(\get_included_files())
            ->saveToFile(PROJECT_BUILD . '/preload.php');

        self::assertFileExists(PROJECT_BUILD . '/preload.php');

        $content = \file_get_contents(PROJECT_BUILD . '/preload.php');
        isNotContain(__FILE__, $content);
    }
}
