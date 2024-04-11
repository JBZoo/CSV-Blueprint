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

use JBZoo\CsvBlueprint\Validators\ErrorSuite;

use function JBZoo\Data\yml;

final class GithubActionsTest extends TestCase
{
    public function testCreateCsvHelp(): void
    {
        $action = yml(PROJECT_ROOT . '/action.yml');

        $availableOptions = \array_keys($action->findArray('inputs'));

        $expectedArgs = ['validate:csv'];

        foreach ($availableOptions as $option) {
            if ($option !== 'extra') {
                $expectedArgs[] = "--{$option}";
            }
            $expectedArgs[] = "\${{ inputs.{$option} }}";
        }

        isSame($expectedArgs, $action->findArray('runs.args'));

        isSame(
            $action->findString('inputs.report.description'),
            'Report format. Available options: ' . \implode(', ', ErrorSuite::getAvaiableRenderFormats()) . '.',
        );
    }

    public function testGitHubActionsReadMe(): void
    {
        $inputs = yml(PROJECT_ROOT . '/action.yml')->findArray('inputs');
        $examples = [
            'csv'         => './tests/**/*.csv',
            'schema'      => './tests/**/*.yml',
            'report'      => "'" . ErrorSuite::REPORT_DEFAULT . "'",
            'apply-all'   => "'auto'",
            'quick'       => "'no'",
            'skip-schema' => "'no'",
            'extra'       => "'options: --ansi'",
        ];

        $expectedMessage = [
            '```yml',
            '- uses: jbzoo/csv-blueprint@master # See the specific version on releases page. `@master` is latest.',
            '  with:',
        ];

        foreach ($inputs as $key => $input) {
            $expectedMessage[] = '    # ' . \trim(\str_replace("\n", "\n    # ", \trim($input['description'])));

            if (isset($input['default'])) {
                $expectedMessage[] = "    # Default value: '{$input['default']}'";
            }

            if (isset($input['default']) && $examples[$key] === $input['default']) {
                $expectedMessage[] = '    # You can skip it.';
            } elseif ($key === 'extra') {
                $expectedMessage[] = '    # You can skip it.';
            }

            if ($key === 'csv' || $key === 'schema') {
                $expectedMessage[] = "    {$key}: '{$examples[$key]}'";
            } else {
                $expectedMessage[] = "    {$key}: {$examples[$key]}";
            }
            $expectedMessage[] = '';
        }

        $text = \trim(\implode("\n", $expectedMessage)) . "\n```";
        Tools::insertInReadme('github-actions-yml', $text);
    }
}
