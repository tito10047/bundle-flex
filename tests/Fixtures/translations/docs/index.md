# AcmeTranslationsBundle

AcmeTranslationsBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\TranslationsBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\TranslationsBundle\GreeterInterface;

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

The greeting is the `acme_translations.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_translations.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\TranslationsBundle\Exception\ExceptionInterface`.

## Translations

The messages of the bundle are in the `AcmeTranslations` translation domain. They
are written in English, and their ids are the English messages: they display
well even when your application has no translator.
Translate them with the translator:

```php
$translator->trans('Welcome!', [], 'AcmeTranslations');
```

To translate them into another language, or to change them, create a file
for the `AcmeTranslations` domain in the `translations/` directory of your
application, e.g. `translations/AcmeTranslations.fr.xlf`. Its messages take precedence over the
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

Run `php bin/console debug:translation en --domain=AcmeTranslations` to list the
messages of the bundle, and `php bin/console lint:xliff translations` to check
your files. See [How to Override any Part of a Bundle](https://symfony.com/doc/current/bundles/override.html#translations).
