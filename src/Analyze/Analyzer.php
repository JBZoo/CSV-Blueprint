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

namespace JBZoo\CsvBlueprint\Analyze;

use JBZoo\CsvBlueprint\Csv\CsvFile;
use JBZoo\CsvBlueprint\Rules\AbstarctRule;
use JBZoo\CsvBlueprint\Utils;
use Symfony\Component\Finder\Finder;

final class Analyzer
{
    public function __construct(
        private string $csvFilename,
    ) {
    }

    public function analyzeCsv(bool $isHeader = true, int $lineLimit = 100): void
    {
        $csv = new CsvFile($this->csvFilename);

        $analyzeResults = [];

        for ($i = 0; $i < $csv->getRealColumNumber(); $i++) {
            $columnValues = [];

            foreach ($csv->getRecords() as $line => $record) {
                if ($line >= $lineLimit) {
                    break;
                }

                if ($isHeader && $line === 0) {
                    continue;
                }

                $columnValues[] = $record[$i] ?? '';
            }
            $analyzeResults[$i] = $this->analyzeColumn($columnValues);
        }

        dump($analyzeResults);
    }

    private function analyzeColumn(array $columnValues): array
    {
        $validRules = [
            'rules'           => [],
            'aggregate_rules' => [],
        ];

        /** @var class-string<AbstarctRule>[] $ruleClassnames */
        foreach ($this->getCellRuleClasses() as $ruleType => $ruleClassnames) {
            /** @var class-string<AbstarctRule> $ruleClassname */
            foreach ($ruleClassnames as $ruleName => $ruleClassname) {
                if ($ruleClassname::testValues($columnValues)) {
                    $validRules[$ruleType][$ruleName] = true;
                }
            }
        }

        if (\count($validRules['rules']) === 0) {
            unset($validRules['rules']);
        }

        if (\count($validRules['aggregate_rules']) === 0) {
            unset($validRules['aggregate_rules']);
        }

        return $validRules;
    }

    /**
     * @return class-string<AbstarctRule>[]
     */
    private function getCellRuleClasses(): array
    {
        static $availableRules = null; // Memoization to avoid multiple file system scans

        if ($availableRules === null) {
            $dirs = ['Cell', 'Aggregate'];

            $availableRules = [
                'rules'           => [],
                'aggregate_rules' => [],
            ];

            foreach ($dirs as $dir) {
                $finder = (new Finder())
                    ->in(__DIR__ . "/../Rules/{$dir}")
                    ->ignoreDotFiles(false)
                    ->ignoreVCS(true)
                    ->name('/\\.php$/')
                    ->files();

                foreach ($finder as $file) {
                    $filename = $file->getFilenameWithoutExtension();
                    $ruleName = Utils::camelToKebabCase($filename);

                    /** @var class-string<AbstarctRule> $ruleClassname */
                    $ruleClassname = "JBZoo\\CsvBlueprint\\Rules\\{$dir}\\{$filename}";

                    if (\class_exists($ruleClassname)) {
                        try {
                            $methodName = $dir === 'Cell' ? 'testValue' : 'testValues';
                            $origClassOfMethod = (new \ReflectionClass($ruleClassname))->getMethod($methodName)->class;
                            if ($ruleClassname !== $origClassOfMethod) {
                                continue;
                            }

                            $key = $dir === 'Cell' ? 'rules' : 'aggregate_rules';
                            $availableRules[$key][$ruleName] = $ruleClassname;
                        } catch (\Exception $exception) {
                            if (Utils::getDebugMode()) {
                                throw $exception;
                            }
                            continue;
                        }
                    }
                }
            }
        }

        return $availableRules;
    }
}
