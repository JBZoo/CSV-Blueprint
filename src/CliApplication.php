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

namespace JBZoo\CsvBlueprint;

class CliApplication extends \JBZoo\Cli\CliApplication
{
    private array $appLogo = [
        '  __________   __   ___  __                  _      __ ',
        ' / ___/ __/ | / /  / _ )/ /_ _____ ___  ____(_)__  / /_',
        '/ /___\ \ | |/ /  / _  / / // / -_) _ \/ __/ / _ \/ __/',
        '\___/___/ |___/  /____/_/\_,_/\__/ .__/_/ /_/_//_/\__/ ',
        '                                /_/                    ',
    ];

    public function getLongVersion(): string
    {
        $logo = '<info>' . \implode("</info>\n<info>", $this->appLogo) . '</info>';

        return "{$logo}\n" . Utils::getVersion(false);
    }
}
