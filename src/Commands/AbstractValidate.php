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

namespace JBZoo\CsvBlueprint\Commands;

use JBZoo\Cli\CliCommand;
use JBZoo\CsvBlueprint\Exception;
use JBZoo\CsvBlueprint\Utils;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;

use function JBZoo\Utils\bool;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
abstract class AbstractValidate extends CliCommand
{
    protected function configure(): void
    {
        $this
            ->addOption(
                'report',
                'r',
                InputOption::VALUE_REQUIRED,
                "Report output format. Available options:\n" .
                Utils::printList(ErrorSuite::getAvaiableRenderFormats(), 'info'),
                ErrorSuite::REPORT_DEFAULT,
            )
            ->addOption(
                'quick',
                'Q',
                InputOption::VALUE_OPTIONAL,
                "Immediately terminate the check at the first error found.\n" .
                "Of course it will speed up the check, but you will get only 1 message out of many.\n" .
                "If any error is detected, the utility will return a non-zero exit code.\n" .
                'Empty value or "yes" will be treated as "true".',
                'no',
            )
            ->addOption(
                'debug',
                null,
                InputOption::VALUE_NONE,
                "It's ONLY for debugging and advanced profiling!",
            );

        parent::configure();
    }

    protected function preparation(): void
    {
        if ($this->isHumanReadableMode()) {
            $this->_('CSV Blueprint: ' . Utils::getVersion(true));
        }

        if ($this->getOptBool('debug')) {
            \define('DEBUG_MODE', true);
        }
    }

    protected function isHumanReadableMode(): bool
    {
        return $this->getReportType() !== ErrorSuite::REPORT_GITLAB
            && $this->getReportType() !== ErrorSuite::REPORT_JUNIT
            && $this->getReportType() !== ErrorSuite::REPORT_TEAMCITY;
    }

    protected function getReportType(): string
    {
        return $this->getOptString('report', ErrorSuite::RENDER_TABLE, ErrorSuite::getAvaiableRenderFormats());
    }

    protected function isQuickMode(): bool
    {
        $value = $this->getOptString('quick');
        return $value === '' || bool($value);
    }

    /**
     * @return SplFileInfo[]
     */
    protected function findFiles(string $option, bool $throwException = true): array
    {
        $patterns = $this->getOptArray($option);
        $filenames = \array_values(Utils::findFiles($patterns));

        if ($throwException && \count($filenames) === 0) {
            throw new Exception('File(s) not found: ' . Utils::printList($patterns));
        }

        return $filenames;
    }

    protected function out(null|array|string $messge, int $indent = 0): void
    {
        if ($messge === null) {
            return;
        }

        if ($this->isHumanReadableMode()) {
            $indent = \str_repeat(' ', $indent);
            $messges = \is_string($messge) ? \explode("\n", $messge) : $messge;

            foreach ($messges as $line) {
                $this->_($indent . $line);
            }
        }
    }

    protected function outReport(ErrorSuite $errorSuite, int $indet = 2): void
    {
        if ($this->getReportType() === ErrorSuite::REPORT_GITHUB) {
            $indet = 0;
        }

        if ($this->isHumanReadableMode()) {
            $this->out($errorSuite->render($this->getReportType()), $indet);
        } else {
            $this->_($errorSuite->render($this->getReportType()));
        }
    }

    protected function renderIssues(string $prefix, int $number, string $filepath, int $indent = 0): void
    {
        $issues = $number === 1 ? 'issue' : 'issues';
        $this->out("{$prefix}<yellow>{$number} {$issues}</yellow> in {$filepath}", $indent);
    }

    protected static function renderPrefix(int $index, int $totalFiles): string
    {
        if ($totalFiles <= 1) {
            return '';
        }

        return \sprintf(
            '(%s/%s) ',
            \str_pad((string)$index, \strlen((string)$totalFiles), ' ', \STR_PAD_LEFT),
            (string)$totalFiles,
        );
    }
}
