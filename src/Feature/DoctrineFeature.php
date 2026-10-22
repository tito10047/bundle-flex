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

namespace Yceruto\BundleFlex\Feature;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * A Doctrine ORM entity (src/Entity/Greeting.php) mapped with XML (config/doctrine/Greeting.orm.xml).
 *
 * As the best practices recommend, the entity is a plain PHP class and its mapping is an XML
 * file of config/doctrine/: unlike attributes, the mapping can be overridden by applications.
 * The entity is concrete, so the bundle works out of the box; a mapped superclass would force
 * every application to create its own entity.
 *
 * AcmeBlogBundle::prependExtension() registers the mapping when DoctrineBundle is enabled, whatever
 * the "auto_mapping" setting of the application. Doctrine stays an optional integration: the
 * bundle works without DoctrineBundle.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#doctrine-entities-documents
 */
final class DoctrineFeature implements FeatureInterface
{
    public const NAME = 'doctrine';

    /**
     * Doctrine ORM 3.4 is the first version supporting the native lazy objects of PHP 8.4,
     * which DoctrineBundle 3 always uses.
     */
    public const ORM_CONSTRAINT = '^3.4';

    /**
     * DoctrineBundle 2.x (PHP 8.1+) supports Symfony 7.4 and 3.x (PHP 8.4+) Symfony 7.4 and 8.x:
     * Composer installs the one the PHP and Symfony versions support.
     */
    public const DOCTRINE_BUNDLE_CONSTRAINT = '^2.18 || ^3.0';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getGroup(): FeatureGroup
    {
        return FeatureGroup::Features;
    }

    public function getQuestion(): string
    {
        return 'Add a Doctrine ORM entity?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        // the mapping of the entity is written for Doctrine ORM 3; DoctrineBundle is the integration
        // of the application, the bundle only registers its mapping when it is enabled
        $composer->require('doctrine/orm', self::ORM_CONSTRAINT);
        $composer->requireDev('doctrine/doctrine-bundle', self::DOCTRINE_BUNDLE_CONSTRAINT);
        $composer->addKeyword('doctrine');

        $blueprint->addFile('features/doctrine/src/Entity/Greeting.php.tpl', 'src/Entity/Greeting.php');
        $blueprint->addFile('features/doctrine/config/doctrine/Greeting.orm.xml.tpl', 'config/doctrine/Greeting.orm.xml');
        $blueprint->addFile('features/doctrine/tests/Entity/GreetingTest.php.tpl', 'tests/Entity/GreetingTest.php');
        $blueprint->addFile('features/doctrine/tests/Integration/Doctrine/GreetingMappingTest.php.tpl', 'tests/Integration/Doctrine/GreetingMappingTest.php');

        $blueprint->slots->addPartial(Slot::BundlePrependExtension, 'features/doctrine/partials/bundle_prepend_extension.php.tpl');

        // the tests use an in-memory SQLite database
        $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Doctrine\Bundle\DoctrineBundle\DoctrineBundle');
        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Composer\InstalledVersions');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/doctrine/partials/test_kernel.php.tpl');
        $blueprint->slots->addValue(ListSlot::CiPhpExtensions, 'pdo_sqlite');

        if ($blueprint->hasFeature(PhpStanFeature::NAME)) {
            // reads the mapping of the entities from the entity manager of the test application
            $composer->requireDev('phpstan/phpstan-doctrine', '^2.0');
            $blueprint->addFile('features/doctrine/tests/object-manager.php.tpl', 'tests/object-manager.php');
            $blueprint->slots->addPartial(Slot::PhpStanParameters, 'features/doctrine/partials/phpstan.neon.tpl');
        }

        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/doctrine/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/doctrine/partials/docs.md.tpl');
    }

    /**
     * Name of the Doctrine mapping of the bundle, e.g. "AcmeBlogBundle" (doctrine.orm.mappings.AcmeBlogBundle).
     *
     * It is the name of the bundle, so the "auto_mapping" of DoctrineBundle does not map the bundle a second time.
     */
    public static function mappingName(BundleNaming $naming): string
    {
        return $naming->className;
    }

    /**
     * Name of the table of the entity, e.g. "acme_blog_greeting": prefixed with the bundle alias,
     * so it cannot collide with the tables of the application or of other bundles.
     */
    public static function tableName(BundleNaming $naming): string
    {
        return $naming->alias.'_greeting';
    }
}
