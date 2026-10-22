<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\EventListenerFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming $naming
 */
$serviceId = EventListenerFeature::serviceId($naming);
?>
## Greeting Header

The bundle adds a greeting header to the responses of your application:

```http
HTTP/1.1 200 OK
<?= EventListenerFeature::headerName($naming) ?>: <?= GreeterContributor::DEFAULT_GREETING ?> World!
```

The header is only added to the response of the main request, and a header with
the same name set by your application is kept as is.

The `<?= $serviceId ?>` service listens to the `<?= EventListenerFeature::EVENT ?>` event, with
the priority <?= EventListenerFeature::PRIORITY ?>. Run `php bin/console debug:event-dispatcher <?= EventListenerFeature::EVENT ?>`
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
        $container->removeDefinition('<?= $serviceId ?>');
    }
}
```
