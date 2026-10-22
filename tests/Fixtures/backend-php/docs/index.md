# AcmeBackendPhpBundle

AcmeBackendPhpBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_backend_php` extension, with their default values:

```yaml
# config/packages/acme_backend_php.yaml
acme_backend_php:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_backend_php.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_backend_php' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_backend_php` to display this
reference, and `php bin/console debug:config acme_backend_php` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\BackendPhpBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\BackendPhpBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `Acme\BackendPhpBundle\Exception\ExceptionInterface`.

## Console Command

The `acme-backend-php:hello` command greets someone with the greeter:

```console
$ php bin/console acme-backend-php:hello Jane

 [OK] Hello Jane!

$ php bin/console acme-backend-php:hello Jane --yell

 [OK] HELLO JANE!
```

Run `php bin/console acme-backend-php:hello --help` to display its usage, and
`php bin/console list acme-backend-php` to list the commands of the bundle.

The command is the `acme_backend_php.command.hello` service. It is loaded lazily:
listing the commands does not create it nor its dependencies.

## Greeting Header

The bundle adds a greeting header to the responses of your application:

```http
HTTP/1.1 200 OK
X-Acme-Backend-Php-Greeting: Hello World!
```

The header is only added to the response of the main request, and a header with
the same name set by your application is kept as is.

The `acme_backend_php.event_listener.greeting_header` service listens to the `kernel.response` event, with
the priority 0. Run `php bin/console debug:event-dispatcher kernel.response`
to display it among the other listeners of this event.

To remove the header from your application, remove the listener in a compiler pass:

```php
// src/Kernel.php
namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        $container->removeDefinition('acme_backend_php.event_listener.greeting_header');
    }
}
```

## Doctrine Entity

The bundle provides the `Acme\BackendPhpBundle\Entity\Greeting` entity, stored in the
`acme_backend_php_greeting` table. It is mapped when your application uses
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

use Acme\BackendPhpBundle\Entity\Greeting;
use Acme\BackendPhpBundle\GreeterInterface;
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
`AcmeBackendPhpBundle` mapping with it:

```yaml
# config/packages/doctrine.yaml
doctrine:
    orm:
        mappings:
            AcmeBackendPhpBundle:
                type: xml
                dir: '%kernel.project_dir%/config/doctrine/AcmeBackendPhp'
                prefix: 'Acme\BackendPhpBundle\Entity'
                is_bundle: false
```

The configuration of your application replaces the mapping registered by the bundle.
To map the entities in another entity manager, disable the mapping of the `default` one
(`AcmeBackendPhpBundle: false`) and configure the same mapping in yours.
