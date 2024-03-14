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

use JBZoo\CIReportConverter\Converters\GithubCliConverter;
use JBZoo\CIReportConverter\Converters\GitLabJsonConverter;
use JBZoo\CIReportConverter\Converters\JUnitConverter;
use JBZoo\CIReportConverter\Converters\TeamCityTestsConverter;
use JBZoo\CIReportConverter\Formats\Source\SourceSuite;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\Utils\Vars;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

final class ErrorSuite
{
    public const REPORT_TEXT     = 'text';
    public const RENDER_TABLE    = 'table';
    public const REPORT_TEAMCITY = 'teamcity';
    public const REPORT_GITLAB   = 'gitlab';
    public const REPORT_GITHUB   = 'github';
    public const REPORT_JUNIT    = 'junit';

    /** @var Error[] */
    private array $errors = [];

    private ?string $csvFilename;

    public function __construct(?string $csvFilename = null)
    {
        $this->csvFilename = $csvFilename;
    }

    public function __toString(): string
    {
        return (string)$this->render(self::REPORT_TEXT);
    }

    public function render(string $mode = self::REPORT_TEXT): ?string
    {
        if ($this->count() === 0) {
            return null;
        }

        $suite = $this->prepareSourceSuite();
        $map   = [
            self::REPORT_TEXT     => fn (): string => $this->renderPlainText(),
            self::RENDER_TABLE    => fn (): string => $this->renderTable(),
            self::REPORT_GITHUB   => static fn (): string => (new GithubCliConverter())->fromInternal($suite),
            self::REPORT_GITLAB   => static fn (): string => (new GitLabJsonConverter())->fromInternal($suite),
            self::REPORT_JUNIT    => static fn (): string => (new JUnitConverter())->fromInternal($suite),
            self::REPORT_TEAMCITY => static fn (): string => (new TeamCityTestsConverter(
                ['show-datetime' => false],
                42,
            ))->fromInternal($suite),
        ];

        if (isset($map[$mode])) {
            return $map[$mode]();
        }

        throw new Exception("Unknown error render mode: {$mode}");
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(?Error $error): self
    {
        if ($error === null) {
            return $this;
        }

        $this->errors[] = $error;

        return $this;
    }

    public function addErrorSuit(?self $errorSuite): self
    {
        if ($errorSuite === null) {
            return $this;
        }

        $this->errors = \array_merge($this->getErrors(), $errorSuite->getErrors());

        return $this;
    }

    public function count(): int
    {
        return \count($this->errors);
    }

    public function get(int $index): ?Error
    {
        return $this->errors[$index] ?? null;
    }

    public static function getAvaiableRenderFormats(): array
    {
        return [
            self::REPORT_TEXT,
            self::RENDER_TABLE,
            self::REPORT_GITHUB,
            self::REPORT_GITLAB,
            self::REPORT_TEAMCITY,
            self::REPORT_JUNIT,
        ];
    }

    private function renderPlainText(): string
    {
        $result = [];

        foreach ($this->errors as $error) {
            $result[] = (string)$error;
        }

        return \implode("\n", $result) . "\n";
    }

    private function renderTable(): string
    {
        $floatingSizes = self::getTableSize();

        $buffer = new BufferedOutput();
        $table  = (new Table($buffer))
            ->setHeaderTitle($this->getTestcaseName())
            ->setFooterTitle($this->getTestcaseName())
            ->setHeaders(['Line', 'id:Column', 'Rule', 'Message'])
            ->setColumnMaxWidth(0, $floatingSizes['line'])
            ->setColumnMaxWidth(1, $floatingSizes['column'])
            ->setColumnMaxWidth(2, $floatingSizes['rule'])
            ->setColumnMaxWidth(3, $floatingSizes['message']);

        foreach ($this->errors as $error) {
            $table->addRow([
                $error->getLine(),
                $error->getColumnName(),
                $error->getRuleCode(),
                $error->getMessage(true),
            ]);
        }

        $table->render();

        return \trim($buffer->fetch()) . "\n";
    }

    private function prepareSourceSuite(): SourceSuite
    {
        $suite = new SourceSuite($this->getTestcaseName());

        foreach ($this->errors as $error) {
            $caseName     = $error->getRuleCode() . ' at column ' . $error->getColumnName();
            $case         = $suite->addTestCase($caseName);
            $case->line   = $error->getLine();
            $case->file   = $this->csvFilename;
            $case->errOut = $error->toCleanString();
        }

        return $suite;
    }

    private function getTestcaseName(): string
    {
        return \pathinfo((string)\realpath((string)$this->csvFilename), \PATHINFO_BASENAME);
    }

    /**
     * Retrieves the size configuration for a table.
     *
     * @return int[]
     */
    private static function getTableSize(): array
    {
        $floatingSizes = [
            'line'    => 10,
            'column'  => 20,
            'rule'    => 20,
            'min'     => 120,
            'max'     => 150,
            'reserve' => 3, // So that the table does not rest on the very edge of the terminal. Just in case.
        ];

        $maxWindowWidth = Vars::limit(
            Utils::autoDetectTerminalWidth() - $floatingSizes['reserve'],
            $floatingSizes['min'],
            $floatingSizes['max'],
        );

        $floatingSizes['message'] = $maxWindowWidth
            - $floatingSizes['line']
            - $floatingSizes['column']
            - $floatingSizes['rule'];

        return $floatingSizes;
    }
}
