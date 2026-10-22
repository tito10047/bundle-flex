# AcmeUxBundle

AcmeUxBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\UxBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\UxBundle\GreeterInterface;

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

The greeting is the `acme_ux.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_ux.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\UxBundle\Exception\ExceptionInterface`.

## Stimulus Controller

The bundle is a [Symfony UX](https://ux.symfony.com/) package: it provides the
`acme/ux-bundle/hello` [Stimulus](https://stimulus.hotwired.dev/) controller
(`assets/src/hello_controller.js`), which closes the element it is attached
to. It is loaded lazily: only when a page uses it.

Use it with the `stimulus_*()` Twig functions of StimulusBundle:

```twig
<section {{ stimulus_controller('acme/ux-bundle/hello') }}>
    <p>Hello Jane!</p>
    <button type="button" {{ stimulus_action('acme/ux-bundle/hello', 'close') }}>Close</button>
</section>
```

Or with the HTML attributes they render:

```html
<section data-controller="acme--ux-bundle--hello">
    <p>Hello Jane!</p>
    <button type="button" data-action="acme--ux-bundle--hello#close">Close</button>
</section>
```

Before closing the element, the controller dispatches the `acme--ux-bundle--hello:close`
event. Cancel it to keep the element open:

```js
document.addEventListener('acme--ux-bundle--hello:close', (event) => {
    if (!confirm('Close the greeting?')) {
        event.preventDefault();
    }
});
```

When your application uses AssetMapper, the bundle adds its `assets/src/` directory to
the AssetMapper paths, under the `@acme/ux-bundle` namespace: run
`php bin/console debug:asset-map` to list its files. Disable the controller in the
`assets/controllers.json` file of your application to stop loading it.
See [Create a UX bundle](https://symfony.com/doc/current/frontend/create_ux_bundle.html).
