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
use JBZoo\Utils\FS;
use JBZoo\Utils\Vars;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\BufferedOutput;

final class ErrorSuite
{
    public const REPORT_TEXT = 'text';
    public const RENDER_TABLE = 'table';
    public const REPORT_TEAMCITY = 'teamcity';
    public const REPORT_GITLAB = 'gitlab';
    public const REPORT_GITHUB = 'github';
    public const REPORT_JUNIT = 'junit';
    public const REPORT_DEFAULT = self::RENDER_TABLE;

    /** @var Error[] */
    private array $errors = [];

    private ?string $csvFilename;

    public function __construct(?string $csvFilename = null)
    {
        $this->csvFilename = $csvFilename;
    }

    /**
     * Returns a string representation of the batch of errors. It's quick user-frienly report.
     * This method should be implemented in classes that want to have a custom string representation.
     * The string returned by this method should be a human-readable representation of the object's state.
     * @return string a string representation of the object
     */
    public function __toString(): string
    {
        return (string)$this->render(self::REPORT_TEXT);
    }

    /**
     * Renders the batch of errors based on the given mode.
     * @param  string      $mode        the render mode
     * @param  bool        $cleanOutput Whether to clean the output. Default is false.
     * @return null|string the rendered batch of errors as a string, or null if there are no errors
     * @throws Exception   if the specified render mode is unknown
     */
    public function render(string $mode = self::REPORT_TEXT, bool $cleanOutput = false): ?string
    {
        if ($this->count() === 0) {
            return null;
        }

        $suite = $this->prepareSourceSuite();
        $map = [
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
            $output = $map[$mode]();

            if ($cleanOutput) {
                return \trim(\strip_tags($output));
            }

            return $output;
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

    /**
     * Adds an error object to the batch of errors.
     * @param  null|Error $error The error object to be added. Pass null to skip adding the error.
     * @return self       the current object instance with the error added, if one was provided; otherwise, returns the
     *                    current object instance
     */
    public function addError(?Error $error): self
    {
        if ($error === null) {
            return $this;
        }

        $this->errors[] = $error;

        return $this;
    }

    /**
     * Adds a batch of errors to the current error suite.
     * @param null|self $errorSuite The error suite to add. If null is passed, the method will do nothing.
     */
    public function addErrorSuit(?self $errorSuite): self
    {
        if ($errorSuite === null) {
            return $this;
        }

        $this->errors = \array_merge($this->getErrors(), $errorSuite->getErrors());

        return $this;
    }

    /**
     * Returns the number of errors in the current batch.
     * @return int the number of errors in the batch
     */
    public function count(): int
    {
        return \count($this->errors);
    }

    /**
     * Retrieves the Error object at the specified index from the batch of errors.
     * Mostly for debugging purposes.
     * @param  int        $index the zero-based index of the Error object to retrieve
     * @return null|Error the Error object at the specified index, or null if the index is out of bounds
     */
    public function get(int $index): ?Error
    {
        return $this->errors[$index] ?? null;
    }

    /**
     * Returns an array of available render formats.
     * This method returns an array of render formats that can be used for generating reports.
     */
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
        $table = (new Table($buffer))
            ->setHeaders(['Line', 'id:Column', 'Rule', 'Message'])
            ->setColumnMaxWidth(0, $floatingSizes['line'])
            ->setColumnMaxWidth(1, $floatingSizes['column'])
            ->setColumnMaxWidth(2, $floatingSizes['rule'])
            ->setColumnMaxWidth(3, $floatingSizes['message'])
            ->setColumnStyle(0, (new TableStyle())->setPadType(\STR_PAD_LEFT));

        foreach ($this->errors as $error) {
            $table->addRow([
                $error->getLine(),
                $error->getColumnName(),
                $error->getRuleCode(),
                $error->getMessage(true),
            ]);
        }

        $table->render();

        return \trim($buffer->fetch());
    }

    private function prepareSourceSuite(): SourceSuite
    {
        $suite = new SourceSuite($this->getTestcaseName());

        foreach ($this->errors as $error) {
            $caseName = $error->getRuleCode() . ' at column ' . $error->getColumnName();
            $case = $suite->addTestCase($caseName);
            $case->line = (int)$error->getLine();
            $case->file = $this->csvFilename;
            $case->errOut = $error->toCleanString();
        }

        return $suite;
    }

    private function getTestcaseName(): string
    {
        $csvFilename = \trim((string)$this->csvFilename);

        if ($csvFilename === '') {
            return '';
        }

        if (!\file_exists($csvFilename)) {
            return $csvFilename;
        }

        return FS::getRelative((string)(new \SplFileInfo($csvFilename))->getRealPath());
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
            'column'  => 30,
            'rule'    => 30,
            'min'     => 120,
            'max'     => 170,
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
