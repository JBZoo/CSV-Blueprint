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

use JBZoo\CsvBlueprint\Rules\Cell\IsAzureNsgCidr;
use JBZoo\PHPUnit\Rules\TestAbstractCellRule;

use function JBZoo\PHPUnit\isSame;

final class IsAzureNsgCidrTest extends TestAbstractCellRule
{
    protected string $ruleClass = IsAzureNsgCidr::class;

    public function testPositive(): void
    {
        $rule = $this->create(true);
        isSame(null, $rule->validate(''));
        isSame('', $rule->test('*'));

        isSame('', $rule->test('10.0.0.1'));
        isSame('', $rule->test('10.0.0.0/8'));
        isSame('', $rule->test('0.0.0.0/0'));
        isSame('', $rule->test('192.168.1.1/32'));
        isSame('', $rule->test('2001:db8::1'));
        isSame('', $rule->test('2001:db8::/32'));
        isSame('', $rule->test('::/0'));
        isSame('', $rule->test('fe80::1/128'));

        isSame('', $rule->test('VirtualNetwork'));
        isSame('', $rule->test('AzureLoadBalancer'));
        isSame('', $rule->test('Internet'));
        isSame('', $rule->test('Storage.WestEurope'));
        isSame('', $rule->test('AzureFrontDoor.Backend'));
        isSame('', $rule->test('M365ManagementActivityApi'));
        isSame('', $rule->test('AzureCloud.uksouth'));
        isSame('', $rule->test('virtualnetwork'));

        $rule = $this->create(false);
        isSame(null, $rule->validate('10.0.0.0/33'));
    }

    public function testNegative(): void
    {
        $rule = $this->create(true);

        $invalid = [
            '10.0.0.0/33',
            '2001:db8::/129',
            '10.0.0/24',
            '256.0.0.0/8',
            '10.0.0.0/',
            '/24',
            '10.0.0.0/-1',
            '10.0.0.0/08',
            "10.0.0.0/8\n",
            '10.0.0.0/8/8',
            '10.0.0.0 /8',
            ' 10.0.0.0/8',
            '10.0.0.0/8,10.0.0.1',
            '**',
            'Storage.',
            '.Storage',
            'Storage..WestEurope',
            'Storage.West.Europe',
            'Virtual Network',
            'Virtual_Network',
            "VirtualNetwork\n",
        ];

        foreach ($invalid as $value) {
            isSame(
                "Value \"{$value}\" is not a valid Azure NSG address prefix",
                $rule->test($value),
                $value,
            );
        }
    }
}
