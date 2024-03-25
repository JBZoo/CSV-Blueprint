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

namespace JBZoo\PHPUnit\Validators;

use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use JBZoo\PHPUnit\TestCase;

use function JBZoo\PHPUnit\isSame;

final class ErrorSuiteTest extends TestCase
{
    public function testGetAvaiableRenderFormats(): void
    {
        isSame([
            'text',
            'table',
            'github',
            'gitlab',
            'teamcity',
            'junit',
        ], ErrorSuite::getAvaiableRenderFormats());
    }
}
