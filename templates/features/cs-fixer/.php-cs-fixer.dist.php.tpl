<?php
/**
 * @var string|null $phpUnitMigrationSet
 */
?>
<?= "<?php\n" ?>

declare(strict_types=1);

/*
 * PHP-CS-Fixer configuration: the Symfony coding standards, and the PHP and PHPUnit
 * modernizations allowed by the lowest supported versions.
 *
 * Run "composer cs" to check the code and "composer cs:fix" to fix it. To change it
 * locally, create a .php-cs-fixer.php file (ignored by Git).
 *
 * See https://symfony.com/doc/current/contributing/code/standards.html
 */

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP8x2Migration' => true,
        '@PHP8x2Migration:risky' => true,
<?php if (null !== $phpUnitMigrationSet): ?>
        '<?= $phpUnitMigrationSet ?>' => true,
<?php endif ?>
        'declare_strict_types' => true,
    ])
    ->setFinder(
        // every PHP file but vendor/ and dot files
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->append([__FILE__]),
    )
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
;
