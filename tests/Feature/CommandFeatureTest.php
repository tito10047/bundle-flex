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

namespace Yceruto\BundleFlex\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\CommandFeature;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class CommandFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new CommandFeature();

        self::assertSame('command', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheCommandItsTestsAndItsDocumentation(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['command' => true]));

        (new CommandFeature())->contribute($blueprint);

        self::assertSame(['symfony/console' => Constraint::SYMFONY], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->composer->getRequireDev());
        self::assertEquals([
            'src/Command/HelloCommand.php' => new FileTemplate('features/command/src/Command/HelloCommand.php.tpl', 'src/Command/HelloCommand.php'),
            'tests/Command/HelloCommandTest.php' => new FileTemplate('features/command/tests/Command/HelloCommandTest.php.tpl', 'tests/Command/HelloCommandTest.php'),
            'tests/Integration/Command/HelloCommandTest.php' => new FileTemplate('features/command/tests/Integration/Command/HelloCommandTest.php.tpl', 'tests/Integration/Command/HelloCommandTest.php'),
        ], $blueprint->getFiles());
        self::assertEquals([new Partial('features/command/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testRegistersTheCommandExplicitlyAndLazily(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['command' => true]));

        (new CommandFeature())->contribute($blueprint);

        self::assertEquals([
            'acme_blog.command.hello' => new ServiceDefinition(
                'acme_blog.command.hello',
                'Acme\BlogBundle\Command\HelloCommand',
                [ServiceArgument::service('acme_blog.greeter')],
                [new ServiceTag('console.command', ['command' => 'acme-blog:hello'])],
            ),
        ], $blueprint->services->getServices());
    }

    #[DataProvider('provideCommandNames')]
    public function testNamesTheCommandAfterTheBundle(string $packageName, string $commandName, string $serviceId): void
    {
        $naming = new BundleNaming($packageName);

        self::assertSame($commandName, CommandFeature::commandName($naming));
        self::assertSame($serviceId, CommandFeature::serviceId($naming));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideCommandNames(): iterable
    {
        yield 'single word' => ['acme/blog-bundle', 'acme-blog:hello', 'acme_blog.command.hello'];
        yield 'several words' => ['my-vendor/foo-bar-bundle', 'my-vendor-foo-bar:hello', 'my_vendor_foo_bar.command.hello'];
    }
}
