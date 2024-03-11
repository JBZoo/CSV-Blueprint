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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

final class ErrorSuite
{
    public const RENDER_TEXT     = 'text';
    public const RENDER_TABLE    = 'table';
    public const RENDER_TEAMCITY = 'teamcity';
    public const RENDER_GITLAB   = 'gitlab';
    public const RENDER_GITHUB   = 'github';
    public const RENDER_JUNIT    = 'junit';

    /** @var Error[] */
    private array $errors = [];

    public function __construct(private ?string $csvFilename = null)
    {
    }

    public function __toString(): string
    {
        return $this->render(self::RENDER_TEXT);
    }

    public function render(string $mode = self::RENDER_TEXT): string
    {
        if ($this->count() === 0) {
            return '';
        }

        $sourceSuite = $this->prepareSourceSuite();
        $map         = [
            self::RENDER_TEXT     => fn (): string => $this->renderPlainText(),
            self::RENDER_TABLE    => fn (): string => $this->renderTable(),
            self::RENDER_GITHUB   => static fn (): string => (new GithubCliConverter())->fromInternal($sourceSuite),
            self::RENDER_GITLAB   => static fn (): string => (new GitLabJsonConverter())->fromInternal($sourceSuite),
            self::RENDER_TEAMCITY => static fn (): string => (new TeamCityTestsConverter())->fromInternal($sourceSuite),
            self::RENDER_JUNIT    => static fn (): string => (new JUnitConverter())->fromInternal($sourceSuite),
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
            self::RENDER_TEXT,
            self::RENDER_TABLE,
            self::RENDER_GITHUB,
            self::RENDER_GITLAB,
            self::RENDER_TEAMCITY,
            self::RENDER_JUNIT,
        ];
    }

    private function renderPlainText(): string
    {
        $result = [];

        foreach ($this->errors as $error) {
            $result[] = (string)$error;
        }

        return \implode("\n", $result);
    }

    private function renderTable(): string
    {
        $buffer = new BufferedOutput();
        $table  = (new Table($buffer))
            ->setHeaderTitle($this->getTestcaseName())
            ->setFooterTitle($this->getTestcaseName())
            ->setHeaders(['Line', 'id:Column', 'Rule', 'Message'])
            ->setColumnMaxWidth(0, 10)
            ->setColumnMaxWidth(1, 20)
            ->setColumnMaxWidth(2, 20)
            ->setColumnMaxWidth(3, 60);

        foreach ($this->errors as $error) {
            $table->addRow([$error->getLine(), $error->getColumnName(), $error->getRuleCode(), $error->getMessage()]);
        }

        $table->render();

        return $buffer->fetch();
    }

    private function prepareSourceSuite(): SourceSuite
    {
        $suite = new SourceSuite($this->getTestcaseName());

        foreach ($this->errors as $error) {
            $caseName     = $error->getRuleCode() . ' at column ' . $error->getColumnName();
            $case         = $suite->addTestCase($caseName);
            $case->line   = $error->getLine();
            $case->file   = $this->csvFilename;
            $case->errOut = $error->getMessage();
        }

        return $suite;
    }

    private function getTestcaseName(): string
    {
        return \pathinfo((string)\realpath((string)$this->csvFilename), \PATHINFO_BASENAME);
    }
}
