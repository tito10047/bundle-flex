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
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * A console command greeting someone with the greeter (src/Command/HelloCommand.php).
 *
 * The command is invokable (#[AsCommand] and __invoke() with #[Argument] and #[Option], Symfony 7.3+):
 * it does not extend Command. It is registered explicitly, with the command name on its
 * "console.command" tag, so the application loads it lazily.
 *
 * @see https://symfony.com/doc/current/console.html
 */
final class CommandFeature implements FeatureInterface
{
    public const NAME = 'command';

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
        return 'Add a console command?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        $blueprint->composer->require('symfony/console', Constraint::SYMFONY);

        $blueprint->addFile('features/command/src/Command/HelloCommand.php.tpl', 'src/Command/HelloCommand.php');
        $blueprint->addFile('features/command/tests/Command/HelloCommandTest.php.tpl', 'tests/Command/HelloCommandTest.php');
        $blueprint->addFile('features/command/tests/Integration/Command/HelloCommandTest.php.tpl', 'tests/Integration/Command/HelloCommandTest.php');

        $blueprint->services->add(new ServiceDefinition(
            self::serviceId($naming),
            $naming->namespace.'\Command\HelloCommand',
            [ServiceArgument::service(GreeterContributor::serviceId($naming))],
            [new ServiceTag('console.command', ['command' => self::commandName($naming)])],
        ));

        $blueprint->slots->addPartial(Slot::DocsSections, 'features/command/partials/docs.md.tpl');
    }

    /**
     * Name of the command, e.g. "acme-blog:hello".
     *
     * The commands of a bundle share one namespace, its slug (like "doctrine:", "make:" or
     * "lexik:jwt:"): "php bin/console list acme-blog" lists them, and they cannot collide
     * with the commands of other bundles or of the application.
     */
    public static function commandName(BundleNaming $naming): string
    {
        return $naming->slug.':hello';
    }

    /**
     * Id of the command service, e.g. "acme_blog.command.hello".
     */
    public static function serviceId(BundleNaming $naming): string
    {
        return $naming->alias.'.command.hello';
    }
}
