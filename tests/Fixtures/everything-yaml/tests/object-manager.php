<?php

declare(strict_types=1);

/*
 * Gives the entity manager of the test application to PHPStan: phpstan/phpstan-doctrine reads
 * the mapping of the entities from it (doctrine.objectManagerLoader in phpstan.dist.neon).
 */

use Doctrine\Persistence\ManagerRegistry;
use MyVendor\EverythingYamlBundle\Tests\App\TestKernel;

// in debug mode, the container and the mapping are refreshed when the mapping files change
$kernel = new TestKernel();
$kernel->boot();

$doctrine = $kernel->getContainer()->get('doctrine');
if (!$doctrine instanceof ManagerRegistry) {
    throw new LogicException('The "doctrine" service must be a ManagerRegistry.');
}

return $doctrine->getManager();
