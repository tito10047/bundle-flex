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

namespace Yceruto\BundleFlex\Blueprint;

/**
 * Text slots of the shared ("master") templates.
 *
 * A slot receives partial templates from any contributor; the master template renders
 * them, ordered by priority (highest first, then registration order), separated by a
 * blank line and indented as the master requires. Partials are written without indentation.
 *
 * Adding a case requires rendering it in the master template that owns it.
 */
enum Slot: string
{
    /**
     * Statements of AcmeBlogBundle::configure(DefinitionConfigurator $definition).
     * The method is only generated when the slot is not empty.
     */
    case BundleConfigure = 'bundle.configure';

    /**
     * Statements of AcmeBlogBundle::loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder),
     * rendered after the import of the services file.
     */
    case BundleLoadExtension = 'bundle.load_extension';

    /**
     * Statements of AcmeBlogBundle::prependExtension(ContainerConfigurator $container, ContainerBuilder $builder).
     * The method is only generated when the slot is not empty.
     */
    case BundlePrependExtension = 'bundle.prepend_extension';

    /**
     * Statements of AcmeBlogBundle::build(ContainerBuilder $container), rendered after "parent::build($container);".
     * The method is only generated when the slot is not empty.
     */
    case BundleBuild = 'bundle.build';

    /**
     * Additional installation steps of README.md ("Step 3", "Step 4", ...), e.g. importing the routes or installing the assets.
     *
     * Each partial is one step: its first line is the title of the step (plain text, without "#" nor "Step N:"),
     * followed by a blank line and the Markdown instructions. The master template numbers the steps.
     *
     * @see https://symfony.com/doc/current/bundles/best_practices.html#installation-instructions
     */
    case ReadmeInstallationSteps = 'readme.installation_steps';

    /** Markdown sections (starting with "## ") of README.md, after the installation instructions. */
    case ReadmeSections = 'readme.sections';

    /**
     * Markdown sections (starting with "## ") of docs/index.md, after the installation section.
     * The configuration reference uses priority 10, so it comes before the usage sections.
     */
    case DocsSections = 'docs.sections';

    /** Statements of the test kernel's configureContainer(ContainerConfigurator $container) method. */
    case TestKernelConfigureContainer = 'test_kernel.configure_container';

    /**
     * Statements of the test kernel's configureRoutes(RoutingConfigurator $routes) method.
     * The method is only generated when the slot is not empty.
     */
    case TestKernelConfigureRoutes = 'test_kernel.configure_routes';

    /**
     * Entries of the "parameters" section of phpstan.dist.neon, e.g. the configuration of a PHPStan extension.
     * Rendered only when the "phpstan" feature is enabled.
     */
    case PhpStanParameters = 'phpstan.parameters';

    /**
     * Service containers needed by the tests (entries of a job's "services" mapping), e.g. a database.
     * Rendered in the "tests" and "coverage" jobs of .github/workflows/ci.yml when the "ci" feature is enabled.
     */
    case CiTestServices = 'ci.test_services';

    /**
     * Steps (YAML list items) run after the dependencies are installed and before the tests, e.g. creating a database.
     * Rendered in the "tests" and "coverage" jobs of .github/workflows/ci.yml when the "ci" feature is enabled.
     */
    case CiTestSteps = 'ci.test_steps';
}
