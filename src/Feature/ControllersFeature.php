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
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\RouteDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * A controller greeting people (src/Controller/HelloController.php) and its route (config/routes.*),
 * which applications import.
 *
 * The controller is a final invokable class, without AbstractController: its dependencies are
 * injected explicitly. It returns plain text. Its functional test only checks the responses.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#controllers
 * @see https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
final class ControllersFeature implements FeatureInterface
{
    public const NAME = 'controllers';

    /** Path of the route, its placeholder has a default value and a requirement. */
    public const ROUTE_PATH = '/hello/{name}';

    /** Name greeted when the URL has none, e.g. "Hello World!" for "/hello". */
    public const DEFAULT_NAME = 'World';

    /** The name must be a single word, e.g. "/hello/Jane"; "/hello/Jane%20Doe" is not found. */
    public const NAME_REQUIREMENT = '\w+';

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
        return 'Add a controller and its route (config/routes.*)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        // "symfony/routing" is required as soon as a route exists
        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/browser-kit', Constraint::SYMFONY);

        $blueprint->addFile('features/controllers/src/Controller/HelloController.php.tpl', 'src/Controller/HelloController.php');
        $blueprint->addFile('features/controllers/tests/Functional/HelloControllerTest.php.tpl', 'tests/Functional/HelloControllerTest.php');

        // the tag makes the service public, as the controller resolver requires:
        // the definition stays private, like every service of the bundle
        $blueprint->services->add(new ServiceDefinition(self::serviceId($naming), self::className($naming), [ServiceArgument::service(GreeterContributor::serviceId($naming))], [
            new ServiceTag('controller.service_arguments'),
        ]));
        $blueprint->routes->add(new RouteDefinition(
            self::routeName($naming),
            self::ROUTE_PATH,
            self::serviceId($naming),
            ['GET'],
            ['name' => self::DEFAULT_NAME],
            ['name' => self::NAME_REQUIREMENT],
        ));

        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Psr\Log\NullLogger');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/controllers/partials/test_kernel.php.tpl');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureRoutes, 'features/controllers/partials/test_kernel_routes.php.tpl');
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/controllers/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/controllers/partials/docs.md.tpl');
    }

    /**
     * Id of the controller service, e.g. "acme_blog.controller.hello".
     */
    public static function serviceId(BundleNaming $naming): string
    {
        return $naming->alias.'.controller.hello';
    }

    /**
     * FQCN of the controller, e.g. "Acme\BlogBundle\Controller\HelloController".
     */
    public static function className(BundleNaming $naming): string
    {
        return $naming->namespace.'\Controller\HelloController';
    }

    /**
     * Name of the route, e.g. "acme_blog_hello".
     */
    public static function routeName(BundleNaming $naming): string
    {
        return $naming->alias.'_hello';
    }

    /**
     * Prefix of the routes in the import examples, e.g. "/acme-blog".
     */
    public static function routePrefix(BundleNaming $naming): string
    {
        return '/'.$naming->slug;
    }
}
