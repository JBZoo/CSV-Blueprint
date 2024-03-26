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

namespace JBZoo\CsvBlueprint\Rules\Cell;

use Respect\Validation\Validator;

final class IsSlug extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => [
                    'true',
                    'Only slug format. Example: "my-slug-123". It can contain letters, numbers, and dashes.',
                ],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!Validator::slug()->validate($cellValue)) {
            return "Value \"{$cellValue}\" is not a valid slug. Expected format \"<green>my-slug-123</green>\"";
        }

        return null;
    }
}
