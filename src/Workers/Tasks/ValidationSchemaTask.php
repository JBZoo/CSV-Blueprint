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

final class ValidationSchemaTask extends AbstractTask
{
    public function __construct(
        private readonly string $schemaFilename,
        private readonly bool $isQuickMode = false,
    ) {
    }

    /**
     * This method performs a series of operations to process the schema and validate it.
     * It creates an ErrorSuite object and initializes it with the provided schema filename.
     * Then, it attempts to create a new Schema object based on the schema filename, and validates it
     * using the isQuickMode flag.
     *
     * If a ParseException occurs during the validation process, the method adds a new error to the ErrorSuite
     * object with the error code 'schema.syntax', the exception message, and the parsed line.
     *
     * If any other type of exception occurs, the method adds a new error to the ErrorSuite object with the
     * error code 'schema.error' and the exception message.
     * Finally, the method returns the ErrorSuite object that contains all the errors.
     *
     * @return ErrorSuite an ErrorSuite object that contains the errors occurred during the process
     */
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
