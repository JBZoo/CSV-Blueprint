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

namespace JBZoo\PHPUnit\Workers;

use JBZoo\CsvBlueprint\Workers\Tasks\AbstractTask;

final class TestTask extends AbstractTask
{
    public const DELAY = 0.1;

    public function __construct(
        private int $id,
    ) {
    }

    public function process(): int
    {
        $timeout = self::DELAY * 1_000_000;
        \usleep((int)$timeout);
        return $this->id;
    }
}
