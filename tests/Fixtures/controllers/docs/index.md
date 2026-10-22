# AcmeControllersBundle

AcmeControllersBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\ControllersBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\ControllersBundle\GreeterInterface;

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

The greeting is the `acme_controllers.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_controllers.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\ControllersBundle\Exception\ExceptionInterface`.

## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `acme_controllers_hello` | `/hello/{name}` | `Acme\ControllersBundle\Controller\HelloController` |

With the `/acme-controllers` prefix, `/acme-controllers/hello/Jane` greets "Hello Jane!", and
`/acme-controllers/hello` greets "Hello World!". The name must be a single word.
The page is plain text.

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_controllers_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_controllers_hello` to display the route.
