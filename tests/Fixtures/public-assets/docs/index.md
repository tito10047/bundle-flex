# AcmePublicAssetsBundle

AcmePublicAssetsBundle for Symfony

## Installation

Follow the [installation instructions](../README.md#installation) of the README.

## Usage

The bundle provides a greeter service. Type-hint `Acme\PublicAssetsBundle\GreeterInterface`
in the constructor of your services to get it injected by autowiring:

```php
namespace App\Service;

use Acme\PublicAssetsBundle\GreeterInterface;

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

The greeting is the `acme_public_assets.greeting` parameter (`Hello` by default).
Change it in the configuration of your application:

```yaml
# config/services.yaml
parameters:
    acme_public_assets.greeting: 'Hi'
```

Every exception thrown by the bundle implements `Acme\PublicAssetsBundle\Exception\ExceptionInterface`.

## Web Assets

The bundle provides a stylesheet, `public/css/acme-public-assets.css`. The
`assets:install` command installs it in the `public/bundles/acmepublicassets/` directory
of your application (see the [installation instructions](../README.md#installation)).
When your application uses AssetMapper, AssetMapper serves it too, with a versioned URL.
Link it in your own templates the same way, `asset()` gives the right URL in both cases:

```twig
<link rel="stylesheet" href="{{ asset('bundles/acmepublicassets/css/acme-public-assets.css') }}">
```
