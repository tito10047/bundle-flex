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
use Yceruto\BundleFlex\Blueprint\ComposerScript;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\PhpCsFixerFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class PhpCsFixerFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new PhpCsFixerFeature();

        self::assertSame('cs-fixer', $feature->getName());
        self::assertSame(FeatureGroup::QualityAssurance, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheConfigurationAndTheScripts(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['cs-fixer' => true]));
        $blueprint->composer->requireDev('phpunit/phpunit', '^11.5 || ^12.0');

        (new PhpCsFixerFeature())->contribute($blueprint);

        self::assertSame('^3.92', $blueprint->composer->getConstraint('friendsofphp/php-cs-fixer'));
        self::assertEquals([
            'cs' => new ComposerScript('cs', ['php-cs-fixer check --diff'], 'Checks the coding standards (PHP-CS-Fixer)', true),
            'cs:fix' => new ComposerScript('cs:fix', ['php-cs-fixer fix'], 'Fixes the coding standards (PHP-CS-Fixer)'),
        ], $blueprint->composer->getScripts());
        self::assertSame(['cs:fix'], $blueprint->composer->getScriptsAfterInstall());
        self::assertEquals([
            '.php-cs-fixer.dist.php' => new FileTemplate('features/cs-fixer/.php-cs-fixer.dist.php.tpl', '.php-cs-fixer.dist.php', ['phpUnitMigrationSet' => '@PHPUnit11x0Migration:risky']),
        ], $blueprint->getFiles());
        self::assertSame(['/.php-cs-fixer.dist.php'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/.php-cs-fixer.php', '/.php-cs-fixer.cache'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }

    /**
     * @return iterable<string, array{string|null, string|null}>
     */
    public static function providePhpUnitConstraints(): iterable
    {
        yield 'no PHPUnit' => [null, null];
        yield 'PHPUnit 11.5 and 12' => ['^11.5 || ^12.0', '@PHPUnit11x0Migration:risky'];
        yield 'newer than every set' => ['^13.0', '@PHPUnit11x0Migration:risky'];
        yield 'PHPUnit 10' => ['^10.5 || ^11.0', '@PHPUnit10x0Migration:risky'];
        yield 'older than every set' => ['^9.6', null];
    }

    #[DataProvider('providePhpUnitConstraints')]
    public function testMatchesThePhpUnitMigrationSetWithTheLowestPhpUnitVersion(?string $constraint, ?string $expected): void
    {
        self::assertSame($expected, PhpCsFixerFeature::getPhpUnitMigrationSet($constraint));
    }
}
