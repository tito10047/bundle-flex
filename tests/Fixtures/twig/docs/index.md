# AcmeTwigBundle

AcmeTwigBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\TwigBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\TwigBundle\GreeterInterface;

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

The greeting is the `acme_twig.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_twig.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\TwigBundle\Exception\ExceptionInterface`.

## Templates

The templates of the bundle are in the `@AcmeTwig` Twig namespace.
`@AcmeTwig/hello.html.twig` displays a greeting. Its only variable is `greeting`,
the greeting to display, e.g. "Hello Jane!". Include it in your templates:

```twig
{{ include('@AcmeTwig/hello.html.twig', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`templates/bundles/AcmeTwigBundle/` directory of your application. For example,
`templates/bundles/AcmeTwigBundle/hello.html.twig` replaces `@AcmeTwig/hello.html.twig`:

```twig
{# templates/bundles/AcmeTwigBundle/hello.html.twig #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# templates/bundles/AcmeTwigBundle/hello.html.twig #}
{% extends '@!AcmeTwig/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).
