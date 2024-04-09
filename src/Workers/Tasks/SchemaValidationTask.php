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

namespace JBZoo\CsvBlueprint\Workers\Tasks;

use JBZoo\CsvBlueprint\Schema;
use JBZoo\CsvBlueprint\Validators\Error;
use JBZoo\CsvBlueprint\Validators\ErrorSuite;
use Symfony\Component\Yaml\Exception\ParseException;

final class SchemaValidationTask extends AbstractTask
{
    public function __construct(
        private readonly string $schemaFilename,
        private readonly bool $isQuickMode = false,
    ) {
    }

    public function process(): ErrorSuite
    {
        $schemaErrors = new ErrorSuite($this->schemaFilename);

        try {
            $schema = new Schema($this->schemaFilename);
            $schemaErrors = $schema->validate($this->isQuickMode);
        } catch (ParseException $e) {
            $schemaErrors->addError(new Error('schema.syntax', $e->getMessage(), '', $e->getParsedLine()));
        } catch (\Throwable $e) {
            $schemaErrors->addError(new Error('schema.error', $e->getMessage()));
        }

        return $schemaErrors;
    }
}
