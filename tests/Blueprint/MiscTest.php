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

namespace JBZoo\PHPUnit\Blueprint;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\Markdown\Markdown;
use JBZoo\PHPUnit\PHPUnit;
use Symfony\Component\Finder\Finder;

use function JBZoo\Data\json;
use function JBZoo\Data\phpArray;
use function JBZoo\Data\yml;
use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class MiscTest extends PHPUnit
{
    public function testKebabToCamelCase(): void
    {
        isSame('Kebab', Utils::kebabToCamelCase('kebab'));
        isSame('KebabCaseString', Utils::kebabToCamelCase('kebab-case-string'));
        isSame('KebabCaseString', Utils::kebabToCamelCase('kebab_case_string'));
    }

    public function testCamelToKebabCase(): void
    {
        isSame('kebab', Utils::camelToKebabCase('Kebab'));
        isSame('kebab_case_string', Utils::camelToKebabCase('KebabCaseString'));
    }

    public function testPrepareRegex(): void
    {
        isSame(null, Utils::prepareRegex(null));
        isSame(null, Utils::prepareRegex(''));
        isSame('/.*/u', Utils::prepareRegex('.*'));
        isSame('#.*#u', Utils::prepareRegex('#.*#u'));
        isSame('/.*/', Utils::prepareRegex('/.*/'));
        isSame('/.*/ius', Utils::prepareRegex('/.*/ius'));
    }

    public function testFullListOfRules(): void
    {
        $rulesInConfig = yml(PROJECT_ROOT . '/schemas-examples/full.yml')->findArray('columns.0.rules');
        $rulesInConfig = \array_keys($rulesInConfig);
        \sort($rulesInConfig);

        $finder = (new Finder())
            ->files()
            ->in(PROJECT_ROOT . '/src/Validators/Rules')
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->name('/\\.php$/');

        foreach ($finder as $file) {
            $ruleName = Utils::camelToKebabCase($file->getFilenameWithoutExtension());
            $excludeRules = [
                'abstarct_rule',
                'exception',
                'rule_exception',
            ];

            if (\in_array($ruleName, $excludeRules, true)) {
                continue;
            }

            $rulesInCode[] = $ruleName;
        }
        \sort($rulesInCode);

        isSame($rulesInCode, $rulesInConfig);
    }

    public function testCsvStrutureDefaultValues(): void
    {
        $defaultsInDoc = yml(PROJECT_ROOT . '/schemas-examples/full.yml')->findArray('csv_structure');

        $schema = new Schema([]);
        $schema->getCsvStructure()->getArrayCopy();

        isSame($defaultsInDoc, $schema->getCsvStructure()->getArrayCopy());
    }

    public function testCheckYmlSchemaExampleInReadme(): void
    {
        $this->testCheckExampleInReadme(
            PROJECT_ROOT . '/schemas-examples/full.yml',
            'yml',
            'YAML format (with comment)',
            12
        );
    }

    public function testCheckPhpSchemaExampleInReadme(): void
    {
        $this->testCheckExampleInReadme(PROJECT_ROOT . '/schemas-examples/full.php', 'php', 'PHP Array as file', 14);
    }

    public function testCheckJsonSchemaExampleInReadme(): void
    {
        $this->testCheckExampleInReadme(PROJECT_ROOT . '/schemas-examples/full.json', 'json', 'JSON Format', 0);
    }

    public function testCompareExamplesWithOrig(): void
    {
        $basepath = PROJECT_ROOT . '/schemas-examples/full';

        $origYml = yml("{$basepath}.yml")->getArrayCopy();

        isSame($origYml, phpArray("{$basepath}.php")->getArrayCopy(), 'PHP config is invalid');
        isSame($origYml, json("{$basepath}.json")->getArrayCopy(), 'JSON config is invalid');
    }

    private function testCheckExampleInReadme(
        string $filepath,
        string $type,
        string $title,
        int $skipFirstLines = 0
    ): void {
        $filepath = \implode(
            "\n",
            \array_slice(\explode("\n", \file_get_contents($filepath)), $skipFirstLines),
        );

        if ($type === 'php') {
            $tmpl = \implode("\n", ["```php", '<?php', $filepath, '```']);
        } else {
            $tmpl = \implode("\n", ["```{$type}", $filepath, '```']);
        }

        $tmpl = $this->getSpoiler("Click to see: {$title}", $tmpl);

        isFileContains($tmpl, PROJECT_ROOT . '/README.md');
    }

    private function getSpoiler(string $title, string $body): string
    {
        return \implode("\n", [
            '<details>',
            "  <summary>{$title}</summary>",
            '',
            "{$body}",
            '',
            '</details>',
            '',
        ]);
    }
}
