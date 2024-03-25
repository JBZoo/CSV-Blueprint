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

final class IsBase64 extends AbstractCellRule
{
    public function getHelpMeta(): array
    {
        return [
            [],
            [
                self::DEFAULT => ['true', 'Validate if a string is Base64-encoded. Example: "cmVzcGVjdCE="'],
            ],
        ];
    }

    public function validateRule(string $cellValue): ?string
    {
        if (!Validator::base64()->validate($cellValue)) {
            return 'Value is not a valid Base64';
        }

        return null;
    }
}
