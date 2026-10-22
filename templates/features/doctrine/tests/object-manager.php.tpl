<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->testsNamespace.'\App\TestKernel',
    'Doctrine\Persistence\ManagerRegistry',
]);
?>
<?= "<?php\n" ?>

declare(strict_types=1);

/*
 * Gives the entity manager of the test application to PHPStan: phpstan/phpstan-doctrine reads
 * the mapping of the entities from it (doctrine.objectManagerLoader in phpstan.dist.neon).
 */

<?= $imports->render() ?>

// in debug mode, the container and the mapping are refreshed when the mapping files change
$kernel = new <?= $imports->name($naming->testsNamespace.'\App\TestKernel') ?>();
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
if (!$doctrine instanceof <?= $imports->name('Doctrine\Persistence\ManagerRegistry') ?>) {
    throw new LogicException('The "doctrine" service must be a ManagerRegistry.');
}

return $doctrine->getManager();
