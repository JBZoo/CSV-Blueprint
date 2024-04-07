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

use JBZoo\CsvBlueprint\Rules\Cell\IsBic;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsBicTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsBic::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));

        $valid = [
            '',
            'DEUTDEFF',        // Deutsche Bank primary office, Frankfurt, Germany
            'NEDSZAJJXXX',     // Nedbank in Johannesburg, South Africa, with primary office code
            'BNPAFRPP',        // BNP Paribas primary office, Paris, France
            'CHASUS33',        // JPMorgan Chase Bank, New York, USA
            'HSBCGB2L',        // HSBC Bank, London, UK
            'UNCRIT2B912',     // UniCredit Bank, Milan, Italy, specific branch
            'DABADKKK',        // Danske Bank, Copenhagen, Denmark
            'NORDEAHH',        // Nordea, Helsinki, Finland
            'AIBKIE2D',        // AIB Bank, Dublin, Ireland
            'CTBAAU2S',        // Commonwealth Bank of Australia, Sydney, Australia
        ];

        foreach ($valid as $value) {
            isSame('', $rule->test($value), $value);
        }

        $rule = $this->create(false);
        isSame(null, $rule->validate(' 1'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            ';',
            ' ',
            'ZZ32 5000 5880 7742',
            '123456789',
            'aBc 123',
            'aBc-123',
        ];

        foreach ($invalid as $value) {
            isSame(
                "The value \"{$value}\" is not a valid BIC number (ISO 9362).",
                $rule->test($value),
                $value,
            );
        }
    }
}
