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

namespace JBZoo\PHPUnit;

final class PhpdocsTest extends TestCase
{
    public function testCheckPublicMethod(): void
    {
        $classes = include PROJECT_ROOT . '/vendor/composer/autoload_classmap.php';
        $projectClasses = [];
        foreach (\array_keys($classes) as $class) {
            if (\str_contains($class, 'JBZoo\CsvBlueprint')) {
                $projectClasses[] = $class;
            }
        }

        $issues = [];
        foreach ($projectClasses as $projectClass) {
            if (\str_contains($projectClass, 'Exception')) {
                continue;
            }

            $reflection = new \ReflectionClass($projectClass);
            $publicMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($publicMethods as $method) {
                if ($method->isDestructor()
                    || $method->isConstructor()
                    || \in_array($method->name, [
                        'getHelp',
                        'getHelpMeta',
                        'validate',
                        'test',
                        'getRuleCode',
                        'parseMode',
                        'validateRule',
                        'analyzeColumnValues',
                        'testValue',
                        'getOptionAsArray',
                    ], true)
                ) {
                    continue;
                }

                // Check if the method's comment is empty
                if (\trim((string)$method->getDocComment()) === '') {
                    // Get fullpath to the file
                    $file = $method->getFileName();

                    if (\str_contains($file, PROJECT_SRC)) {
                        $issues[] =
                            $file . ':' . $method->getStartLine() . "\n" .
                            "Method '{$projectClass}::{$method->name}' has no PHPDoc comment";
                    }
                }
            }
        }

        if ($count = \count($issues)) {
            self::fail("Found {$count} methods without PHPDoc:\n" . \implode("\n\n", $issues));
        }

        success('All public methods have PHPDoc comments');
    }
}
