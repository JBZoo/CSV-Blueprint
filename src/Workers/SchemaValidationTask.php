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

namespace JBZoo\CsvBlueprint\Workers;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;
use JBZoo\CsvBlueprint\Schema;

class SchemaValidationTask implements Task
{
    public function __construct(
        private readonly string $schemaFilename,
    ) {
    }

    public function run(Channel $channel, Cancellation $cancellation): string
    {
        //Cli::out('Validating schema: ' . $this->schemaFilename);
        return (string)(new Schema($this->schemaFilename))->validate();
    }
}
