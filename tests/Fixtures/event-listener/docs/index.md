# AcmeEventListenerBundle

AcmeEventListenerBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\EventListenerBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\EventListenerBundle\GreeterInterface;

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

The greeting is the `acme_event_listener.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_event_listener.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\EventListenerBundle\Exception\ExceptionInterface`.

## Greeting Header

The bundle adds a greeting header to the responses of your application:

```http
HTTP/1.1 200 OK
X-Acme-Event-Listener-Greeting: Hello World!
```

The header is only added to the response of the main request, and a header with
the same name set by your application is kept as is.

The `acme_event_listener.event_listener.greeting_header` service listens to the `kernel.response` event, with
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
        $container->removeDefinition('acme_event_listener.event_listener.greeting_header');
    }
}
```
