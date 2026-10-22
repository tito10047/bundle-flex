<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\DoctrineFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming $naming
 */
$entity = $naming->namespace.'\Entity\Greeting';
$mapping = DoctrineFeature::mappingName($naming);
?>
## Doctrine Entity

The bundle provides the `<?= $entity ?>` entity, stored in the
`<?= DoctrineFeature::tableName($naming) ?>` table. It is mapped when your application uses
[Doctrine ORM with DoctrineBundle](https://symfony.com/doc/current/doctrine.html):

```console
composer require symfony/orm-pack
```

The bundle registers the mapping of its entities in the `default` entity manager: you
don't have to configure anything, whatever the `auto_mapping` setting of your application.
Run `php bin/console doctrine:mapping:info` to check it, and create the table as explained
in the [installation instructions](../README.md#installation).

Use the entity like the ones of your application:

```php
namespace App\Service;

use <?= $entity ?>;
use <?= GreeterContributor::interfaceName($naming) ?>;
use Doctrine\ORM\EntityManagerInterface;

final class GreetingRecorder
{
    public function __construct(
        private readonly GreeterInterface $greeter,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function greet(string $name): Greeting
    {
        $greeting = new Greeting($name, $this->greeter->greet($name));
        $this->entityManager->persist($greeting);
        $this->entityManager->flush();

        return $greeting;
    }
}
```

### Overriding the Mapping

The mapping is defined in XML, in `config/doctrine/Greeting.orm.xml`. To change it, e.g.
to rename the table, copy this file to a directory of your application and redefine the
`<?= $mapping ?>` mapping with it:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        mappings:
            <?= $mapping ?>:
                type: xml
                dir: '%kernel.project_dir%/config/doctrine/<?= $naming->shortName ?>'
                prefix: '<?= $naming->namespace ?>\Entity'
                is_bundle: false
```

The configuration of your application replaces the mapping registered by the bundle.
To map the entities in another entity manager, disable the mapping of the `default` one
(`<?= $mapping ?>: false`) and configure the same mapping in yours.
