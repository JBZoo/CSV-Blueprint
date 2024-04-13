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

final class PackageTest extends \JBZoo\Codestyle\PHPUnit\AbstractPackageTest
{
    protected string $packageName = 'Csv-Blueprint';
    protected string $composerPhpVersion = '^8.2';

    protected array $params = [
        'packagist_latest_stable_version'   => true,
        'packagist_latest_unstable_version' => true,
        'packagist_license'                 => true,
        'packagist_version'                 => true,

        'packagist_dependents' => false,
        'packagist_suggesters' => false,

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

        'github_latest_release' => true,

        'github_actions_demo'           => true,
        'github_actions_release_docker' => true,

        'docker_pulls'      => true,
        'docker_image_size' => true,

        'psalm_coverage' => true,
        'psalm_level'    => false,
        'codacy'         => true,
        'codefactor'     => false,
        'sonarcloud'     => true,
        'coveralls'      => true,
        'circle_ci'      => true,

        'sonarqube_coverage' => true,
        'sonarqube_bugs'     => true,
        'sonarqube_smells'   => true,
    ];

    protected array $badgesTemplate = [
        'github_actions',
        'github_actions_demo',
        'psalm_coverage',
        'sonarqube_coverage',
        'sonarqube_bugs',
        'sonarqube_smells',
        'docker_pulls',
    ];

    protected function setUp(): void
    {
        $this->excludePaths[] = 'assets';

        parent::setUp();
    }

    public function testGithubActionsWorkflow(): void
    {
        success('It uses different workflows for CI');
    }

    public function testComposerType(): void
    {
        $composer = json(PROJECT_ROOT . '/composer.json');
        isSame('project', $composer->find('type'));
    }

    public function testReadmeHeader(): void
    {
        $expectedBadges = [];

        foreach ($this->badgesTemplate as $badgeName) {
            if ($badgeName === '__BR__') {
                $expectedBadges[$badgeName] = ' ';
            } else {
                $testMethod = 'checkBadge' . \str_replace('_', '', \ucwords($badgeName, '_'));

                if (\method_exists($this, $testMethod)) {
                    $tmpBadge = $this->{$testMethod}();
                    if ($tmpBadge !== null) {
                        $expectedBadges[$badgeName] = $tmpBadge;
                    }
                } else {
                    fail("Method not found: '{$testMethod}'");
                }
            }
        }

        $expectedBadgeLine = \implode("\n", $expectedBadges);

        Tools::insertInReadme('top-badges', $expectedBadgeLine);
    }

    protected function checkBadgeGithubActionsDemo(): ?string
    {
        $path = 'https://github.com/__VENDOR_ORIG__/__PACKAGE_ORIG__/actions/workflows';

        return $this->getPreparedBadge(
            $this->getBadge(
                'CI',
                $path . '/demo.yml/badge.svg',
                $path . '/demo.yml',
            ),
        );
    }

    protected function checkBadgeGithubActionsReleaseDocker(): ?string
    {
        $path = 'https://github.com/__VENDOR_ORIG__/__PACKAGE_ORIG__/actions/workflows';

        return $this->getPreparedBadge(
            $this->getBadge(
                'CI',
                $path . '/publish.yml/badge.svg',
                $path . '/publish.yml',
            ),
        );
    }

    protected function checkBadgeDockerPulls(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'Docker Pulls',
                'https://img.shields.io/docker/pulls/__VENDOR__/__PACKAGE__.svg',
                'https://hub.docker.com/r/__VENDOR__/__PACKAGE__/tags',
            ),
        );
    }

    protected function checkBadgeDockerImageSize(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'Docker Image Size',
                'https://img.shields.io/docker/image-size/jbzoo/csv-blueprint',
                'https://hub.docker.com/r/__VENDOR__/__PACKAGE__/tags',
            ),
        );
    }

    protected function checkBadgeGithubLatestRelease(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'GitHub Release',
                'https://img.shields.io/github/v/release/jbzoo/csv-blueprint?label=Latest',
                'https://github.com/__VENDOR__/__PACKAGE__/releases',
            ),
        );
    }

    protected function checkBadgeSonarqubeBugs(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'Bugs',
                'https://sonarcloud.io/api/project_badges/measure?project=JBZoo_Csv-Blueprint&metric=bugs',
                'https://sonarcloud.io/project/issues?resolved=false&id=JBZoo_Csv-Blueprint',
            ),
        );
    }

    protected function checkBadgeSonarqubeSmells(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'Code smells',
                'https://sonarcloud.io/api/project_badges/measure?project=JBZoo_Csv-Blueprint&metric=code_smells',
                'https://sonarcloud.io/project/issues?resolved=false&id=JBZoo_Csv-Blueprint',
            ),
        );
    }

    protected function checkBadgeSonarqubeCoverage(): ?string
    {
        return $this->getPreparedBadge(
            $this->getBadge(
                'Coverage',
                'https://sonarcloud.io/api/project_badges/measure?project=JBZoo_Csv-Blueprint&metric=coverage',
                'https://sonarcloud.io/code?id=JBZoo_Csv-Blueprint&selected=JBZoo_Csv-Blueprint%3Asrc',
            ),
        );
    }

    protected function getTitle(): string
    {
        return '# CSV Blueprint';
    }
}
