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

namespace JBZoo\CsvBlueprint\Analyze;

use JBZoo\CsvBlueprint\Rules\Cell\IsLatitude;
use JBZoo\Data\AbstractData;

use function JBZoo\Data\data;

final class RuleOptimizer
{
    /**
     * Remove unnecessary validation rules to simplify readability and comprehension,
     * as well as not losing the strictness of validation.
     * @param  array $rules the rules to be processed
     * @return array the modified rules array
     */
    public static function optimize(array $rules): array
    {
        $rules = data($rules);

        if ($rules->is('not_empty', false, true) && $rules->is('length', 0, true)) {
            return ['exact_value' => ''];  // Empty string
        }

        $rules = self::specific($rules);
        $rules = self::numberVsString($rules);
        $rules = self::intVsFloat($rules);
        $rules = self::fixNumericTypes($rules);
        $rules = self::dates($rules);
        $rules = self::coords($rules);
        $rules = self::basicStrings($rules);

        return \array_filter($rules->getArrayCopy(), static fn ($value) => $value !== null);
    }

    private static function isAnyIs(AbstractData $rules): bool
    {
        return \count(
            \array_filter(
                \array_keys($rules->getArrayCopy()),
                static fn ($key) => \strpos((string)$key, 'is_') === 0,
            ),
        ) > 1;
    }

    private static function numberVsString(AbstractData $rules): AbstractData
    {
        if (
            $rules->has('is_float')
            || $rules->has('is_int')
            || $rules->has('is_hex')
            || $rules->has('is_binary')
        ) {
            $rules = $rules
                ->remove('length_min')
                ->remove('length_max')
                ->remove('is_lowercase')
                ->remove('is_uppercase')
                ->remove('is_capitalize')
                ->remove('is_slug')
                ->remove('is_geohash')
                ->remove('is_alnum');
        }

        return $rules;
    }

    private static function intVsFloat(AbstractData $rules): AbstractData
    {
        if (
            $rules->has('is_float')
            && $rules->has('is_int')
            && (
                $rules->is('precision', 0, true)
                || $rules->is('precision_max', 0, true)
            )
        ) {
            $rules = $rules
                ->remove('is_float')
                ->remove('precision')
                ->remove('precision_min')
                ->remove('precision_max');
        }

        if (!$rules->has('is_float') && !$rules->has('is_int')) {
            $rules = $rules
                ->remove('precision')
                ->remove('precision_min')
                ->remove('precision_max');
        }

        if ($rules->has('is_float') && $rules->has('is_int')) {
            if (!$rules->has('precision') && !$rules->has('precision_max')) {
                $rules = $rules->remove('is_float');
            }
            if ($rules->has('precision') || $rules->has('precision_max')) {
                $rules = $rules->remove('is_int');
            }
        }

        return $rules;
    }

    private static function dates(AbstractData $rules): AbstractData
    {
        if (
            $rules->has('is_date')
            || ($rules->has('is_lowercase') && $rules->has('is_uppercase'))
            || $rules->has('is_int')
            || $rules->has('if_float')
        ) {
            $rules = $rules
                ->remove('is_lowercase')
                ->remove('is_uppercase')
                ->remove('is_capitalize');
        }

        return $rules;
    }

    private static function coords(AbstractData $rules): AbstractData
    {
        if (
            $rules->has('is_latitude')
            && $rules->getInt('num_min') >= IsLatitude::MIN_VALUE
            && $rules->getInt('num_max') <= IsLatitude::MAX_VALUE
        ) {
            $rules = $rules->remove('is_longitude');
        }

        return $rules;
    }

    private static function basicStrings(AbstractData $rules): AbstractData
    {
        if ($rules->has('is_lowercase') || $rules->has('is_uppercase')) {
            $rules = $rules->remove('is_capitalize');
        }

        if ($rules->has('hash')) {
            $rules = $rules
                ->remove('is_geohash')
                ->remove('is_base64')
                ->remove('is_alnum');
        }

        return $rules;
    }

    private static function specific(AbstractData $rules): AbstractData
    {
        if ($rules->has('is_uuid')) {
            $rules = $rules
                ->remove('is_trimmed')
                ->remove('is_slug')
                ->remove('length');
        }

        if (self::isAnyIs($rules)) {
            $rules = $rules->remove('is_password_safe_chars');
        }

        return $rules;
    }

    private static function fixNumericTypes(AbstractData $rules): AbstractData
    {
        if ($rules->has('is_int')) {
            if ($rules->has('num_min')) {
                $rules = $rules->set('num_min', (int)$rules->get('num_min'));
                $rules = $rules->set('num_max', (int)$rules->get('num_max'));
            }
            if ($rules->has('num')) {
                $rules = $rules->set('num', (int)$rules->get('num'));
            }
        }

        if ($rules->has('is_float')) {
            if ($rules->has('num_min')) {
                $rules = $rules->set('num_min', (float)$rules->get('num_min'));
                $rules = $rules->set('num_max', (float)$rules->get('num_max'));
            }
            if ($rules->has('num')) {
                $rules = $rules->set('num', (float)$rules->get('num'));
            }
        }

        return $rules;
    }
}
