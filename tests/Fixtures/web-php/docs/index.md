# AcmeWebPhpBundle

AcmeWebPhpBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Configuration

The bundle works without configuration. These are the options of the
`acme_web_php` extension, with their default values:

```yaml
# config/packages/acme_web_php.yaml
acme_web_php:
    # The word people are greeted with, e.g. "Hello" in "Hello Jane!".
    greeting: 'Hello' # Example: 'Hi'
```

The same configuration in PHP:

```php
// config/packages/acme_web_php.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return App::config([
    'acme_web_php' => [
        'greeting' => 'Hello',
    ],
]);
```

Run `php bin/console config:dump-reference acme_web_php` to display this
reference, and `php bin/console debug:config acme_web_php` to display the
configuration of your application.

## Usage

The bundle provides a greeter service. Type-hint `Acme\WebPhpBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\WebPhpBundle\GreeterInterface;

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

Every exception thrown by the bundle implements `Acme\WebPhpBundle\Exception\ExceptionInterface`.

## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `acme_web_php_hello` | `/hello/{name}` | `Acme\WebPhpBundle\Controller\HelloController` |

With the `/acme-web-php` prefix, `/acme-web-php/hello/Jane` greets "Hello Jane!", and
`/acme-web-php/hello` greets "Hello World!". The name must be a single word.
The page is rendered from the `@AcmeWebPhp/hello.html.twig` template, see [Templates](#templates).

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('acme_web_php_hello', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router acme_web_php_hello` to display the route.

## Templates

The templates of the bundle are in the `@AcmeWebPhp` Twig namespace.
`@AcmeWebPhp/hello.html.twig` renders the greeting page. Its only variable is
`greeting`, the greeting to display, e.g. "Hello Jane!". You can also include it
in your own templates:

```twig
{{ include('@AcmeWebPhp/hello.html.twig', {greeting: 'Hello Jane!'}) }}
```

The templates extend no layout: they display well anywhere in your pages.

### Overriding the templates

To change a template, create a template with the same name in the
`templates/bundles/AcmeWebPhpBundle/` directory of your application. For example,
`templates/bundles/AcmeWebPhpBundle/hello.html.twig` replaces `@AcmeWebPhp/hello.html.twig`:

```twig
{# templates/bundles/AcmeWebPhpBundle/hello.html.twig #}
<p class="greeting">{{ greeting }}</p>
```

To only change its `title` block, extend the original template: the `!` prefix
always refers to the template of the bundle.

```twig
{# templates/bundles/AcmeWebPhpBundle/hello.html.twig #}
{% extends '@!AcmeWebPhp/hello.html.twig' %}

{% block title %}Nice to meet you!{% endblock %}
```

Clear the cache after creating an overriding template for the first time:
`php bin/console cache:clear`.
See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#templates).

## Translations

The messages of the bundle are in the `AcmeWebPhp` translation domain. They
are written in English, and their ids are the English messages: they display
well even when your application has no translator.
The [templates](#templates) translate them with the `trans` filter:

```twig
{{ 'Welcome!'|trans({}, 'AcmeWebPhp') }}
```

To translate them into another language, or to change them, create a file
for the `AcmeWebPhp` domain in the `translations/` directory of your
application, e.g. `translations/AcmeWebPhp.fr.xlf`. Its messages take precedence over the
messages of the bundle:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">
    <file source-language="en" target-language="fr" datatype="plaintext" original="file.ext">
        <body>
            <trans-unit id="1">
                <source>Welcome!</source>
                <target>Bienvenue !</target>
            </trans-unit>
        </body>
    </file>
</xliff>
```

Run `php bin/console debug:translation en --domain=AcmeWebPhp` to list the
messages of the bundle, and `php bin/console lint:xliff translations` to check
your files. See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#translations).

## Web Assets

The bundle provides a stylesheet, `public/css/acme-web-php.css`. The
`assets:install` command installs it in the `public/bundles/acmewebphp/` directory
of your application (see the [installation instructions](../README.md#installation)).
When your application uses AssetMapper, AssetMapper serves it too, with a versioned URL.
The [templates](#templates) link it, with the `asset()` function of the
[Asset component](https://symfony.com/doc/current/components/asset.html).
Link it in your own templates the same way, `asset()` gives the right URL in both cases:

```twig
<link rel="stylesheet" href="{{ asset('bundles/acmewebphp/css/acme-web-php.css') }}">
```

## Stimulus Controller

The bundle is a [Symfony UX](https://ux.symfony.com/) package: it provides the
`acme/web-php-bundle/hello` [Stimulus](https://stimulus.hotwired.dev/) controller
(`assets/src/hello_controller.js`), which closes the element it is attached
to. It is loaded lazily: only when a page uses it.
The [templates](#templates) use it to close the greeting.

Use it with the `stimulus_*()` Twig functions of StimulusBundle:

```twig
<section {{ stimulus_controller('acme/web-php-bundle/hello') }}>
    <p>Hello Jane!</p>
    <button type="button" {{ stimulus_action('acme/web-php-bundle/hello', 'close') }}>Close</button>
</section>
```

Or with the HTML attributes they render:

```html
<section data-controller="acme--web-php-bundle--hello">
    <p>Hello Jane!</p>
    <button type="button" data-action="acme--web-php-bundle--hello#close">Close</button>
</section>
```

Before closing the element, the controller dispatches the `acme--web-php-bundle--hello:close`
event. Cancel it to keep the element open:

```js
document.addEventListener('acme--web-php-bundle--hello:close', (event) => {
    if (!confirm('Close the greeting?')) {
        event.preventDefault();
    }
});
```

When your application uses AssetMapper, the bundle adds its `assets/src/` directory to
the AssetMapper paths, under the `@acme/web-php-bundle` namespace: run
`php bin/console debug:asset-map` to list its files. Disable the controller in the
`assets/controllers.json` file of your application to stop loading it.
See [Create a UX bundle](https://symfony.com/doc/current/frontend/create_ux_bundle.html).
