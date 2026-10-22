# AcmeDoctrineEntityBundle

AcmeDoctrineEntityBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\DoctrineEntityBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\DoctrineEntityBundle\GreeterInterface;

final class Welcome
{
    public function __construct(
        private readonly GreeterInterface $greeter,
    ) {
    }

    public function welcome(string $name): string
    {
        return $this->greeter->greet($name); // "Hello Jane!" for "Jane"
    }
}
```

The greeting is the `acme_doctrine_entity.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_doctrine_entity.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\DoctrineEntityBundle\Exception\ExceptionInterface`.

## Doctrine Entity

The bundle provides the `Acme\DoctrineEntityBundle\Entity\Greeting` entity, stored in the
`acme_doctrine_entity_greeting` table. It is mapped when your application uses
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

use Acme\DoctrineEntityBundle\Entity\Greeting;
use Acme\DoctrineEntityBundle\GreeterInterface;
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
`AcmeDoctrineEntityBundle` mapping with it:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        mappings:
            AcmeDoctrineEntityBundle:
                type: xml
                dir: '%kernel.project_dir%/config/doctrine/AcmeDoctrineEntity'
                prefix: 'Acme\DoctrineEntityBundle\Entity'
                is_bundle: false
```

The configuration of your application replaces the mapping registered by the bundle.
To map the entities in another entity manager, disable the mapping of the `default` one
(`AcmeDoctrineEntityBundle: false`) and configure the same mapping in yours.
