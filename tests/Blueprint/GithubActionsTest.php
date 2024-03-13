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

use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\PHPUnit\PHPUnit;

use function JBZoo\Data\yml;
use function JBZoo\PHPUnit\isFileContains;
use function JBZoo\PHPUnit\isSame;

final class GithubActionsTest extends PHPUnit
{
    public function testCreateCsvHelp(): void
    {
        $action = yml(PROJECT_ROOT . '/action.yml');

        $availableOptions = \array_keys($action->findArray('inputs'));

        $expectedArgs = ['validate:csv'];

        foreach ($availableOptions as $option) {
            $expectedArgs[] = "--{$option}";
            $expectedArgs[] = '${{ inputs.' . $option . ' }}';
        }

        $expectedArgs[] = '--ansi';
        $expectedArgs[] = '-vvv';

        isSame($expectedArgs, $action->findArray('runs.args'));

        isSame(
            $action->findString('inputs.report.description'),
            'Report format. Available options: ' . \implode(', ', ErrorSuite::getAvaiableRenderFormats()),
        );
    }

    public function testGitHubActionsReadMe(): void
    {
        $inputs   = yml(PROJECT_ROOT . '/action.yml')->findArray('inputs');
        $examples = [
            'csv'    => './tests/**/*.csv',
            'schema' => './tests/schema.yml',
            'report' => 'github',
            'quick'  => 'no',
        ];

        $expectedMessage = [
            '```yml',
            '- uses: jbzoo/csv-blueprint # See the specific version on releases page',
            '  with:',
        ];

        foreach ($inputs as $key => $input) {
            $expectedMessage[] = '    # ' . \trim($input['description']);

            if (isset($input['default'])) {
                $expectedMessage[] = "    # Default value: {$input['default']}";
            }

            if (isset($input['default']) && $examples[$key] === $input['default']) {
                $expectedMessage[] = '    # You can skip it';
            } elseif (isset($input['required']) && $input['required']) {
                $expectedMessage[] = '    # Required: true';
            }

            $expectedMessage[] = "    {$key}: {$examples[$key]}";
            $expectedMessage[] = '';
        }

        $expectedMessage[] = '```';

        isFileContains(\implode("\n", $expectedMessage), PROJECT_ROOT . '/README.md');
    }
}
