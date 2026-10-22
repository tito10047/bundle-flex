# AcmeCommandBundle

AcmeCommandBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\CommandBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\CommandBundle\GreeterInterface;

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

The greeting is the `acme_command.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_command.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\CommandBundle\Exception\ExceptionInterface`.

## Console Command

The `acme-command:hello` command greets someone with the greeter:

```console
$ php bin/console acme-command:hello Jane

 [OK] Hello Jane!

$ php bin/console acme-command:hello Jane --yell

 [OK] HELLO JANE!
```

Run `php bin/console acme-command:hello --help` to display its usage, and
`php bin/console list acme-command` to list the commands of the bundle.

The command is the `acme_command.command.hello` service. It is loaded lazily:
listing the commands does not create it nor its dependencies.
