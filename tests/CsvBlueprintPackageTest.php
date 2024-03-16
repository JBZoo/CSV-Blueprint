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

namespace JBZoo\PHPUnit;

use function JBZoo\Data\json;

final class CsvBlueprintPackageTest extends \JBZoo\Codestyle\PHPUnit\AbstractPackageTest
{
    protected string $packageName = 'Csv-Blueprint';

    protected array $params = [
        // Packagist
        'packagist_latest_stable_version'   => true,
        'packagist_latest_unstable_version' => true,
        'packagist_license'                 => true,
        'packagist_version'                 => true,

        'packagist_dependents' => true,
        'packagist_suggesters' => true,

        'packagist_downloads_total'   => true,
        'packagist_downloads_daily'   => true,
        'packagist_downloads_monthly' => true,

        'packagist_composerlock'  => true,
        'packagist_gitattributes' => true,

        'github_issues'  => true,
        'github_license' => true,
        'github_forks'   => true,
        'github_stars'   => true,
        'github_actions' => true,

        'github_actions_demo'           => true,
        'github_actions_release_docker' => true,

        'docker_pulls' => true,

        'psalm_coverage' => true,
        'psalm_level'    => true,
        'codacy'         => true,
        'codefactor'     => true,
        'sonarcloud'     => true,
        'coveralls'      => true,
        'circle_ci'      => true,
    ];

    protected array $badgesTemplate = [
        'github_actions',
        'github_actions_demo',
        'github_actions_release_docker',
        'docker_build',
        'codecov',
        'coveralls',
        'psalm_coverage',
        'psalm_level',
        'codefactor',
        'scrutinizer',
        '__BR__',
        'packagist_latest_stable_version',
        'packagist_downloads_total',
        'docker_pulls',
        'packagist_dependents',
        'visitors',
        'github_license',
    ];

    protected function setUp(): void
    {
        $this->excludePaths[] = 'assets';

        parent::setUp();
    }

    public static function testComposerOptimizeAutoloader(): void
    {
        $composer = json(PROJECT_ROOT . '/composer.json');
        isSame(false, $composer->find('config.optimize-autoloader'));
    }

    protected function checkBadgeGithubActionsDemo(): ?string
    {
        $path = 'https://github.com/__VENDOR_ORIG__/__PACKAGE_ORIG__/actions/workflows';

        return $this->getPreparedBadge(
            $this->getBadge(
                'CI',
                $path . '/demo.yml/badge.svg?branch=master',
                $path . '/demo.yml?query=branch%3Amaster',
            ),
        );
    }

    protected function checkBadgeGithubActionsReleaseDocker(): ?string
    {
        $path = 'https://github.com/__VENDOR_ORIG__/__PACKAGE_ORIG__/actions/workflows';

        return $this->getPreparedBadge(
            $this->getBadge(
                'CI',
                $path . '/release-docker.yml/badge.svg?branch=master',
                $path . '/release-docker.yml?query=branch%3Amaster',
            ),
        );
    }
}
