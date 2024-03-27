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

namespace JBZoo\PHPUnit\Rules\Cell;

use JBZoo\CsvBlueprint\Rules\Cell\Hash;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;
use JBZoo\Utils\Str;

use function JBZoo\PHPUnit\isSame;

final class HashTest extends TestAbstractCellRule
{
    protected string $ruleClass = Hash::class;

    public function testPositive(): void
    {
        $algos = \hash_algos();
        $attempts = 100;

        foreach ($algos as $algo) {
            $rule = $this->create($algo);
            isSame('', $rule->test(''));

            foreach (\range(1, $attempts) as $i) {
                $hash = \hash($algo, Str::random(32));
                $strlen = \strlen($hash);
                isSame('', $rule->test($hash), "'{$algo}' => \$this->getRegex({$strlen}),");
            }
        }
    }

    public function testNegative(): void
    {
        $rule = $this->create('qwerty');
        isSame(
            'The algorithm "qwerty" is not supported.',
            $rule->test('qwerty'),
        );
    }
}
