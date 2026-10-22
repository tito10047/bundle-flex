<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\BundleFlex\Contributor\Core;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Contributor\ContributorInterface;
use Yceruto\BundleFlex\Options\ConfigFormat;

/**
 * Dependencies and keywords every bundle has.
 *
 * Only the components the bundle class and its configuration files use are required,
 * not the whole framework: applications bring FrameworkBundle themselves.
 */
final class PackageContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        $composer->addKeyword('symfony');
        $composer->addKeyword('symfony-bundle');

        $composer->require('php', Constraint::PHP);
        $composer->require('symfony/config', Constraint::SYMFONY);
        $composer->require('symfony/dependency-injection', Constraint::SYMFONY);
        $composer->require('symfony/http-kernel', Constraint::SYMFONY);

        if (ConfigFormat::Yaml === $blueprint->options->configFormat) {
            $composer->require('symfony/yaml', Constraint::SYMFONY);
        }
    }
}
