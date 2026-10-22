<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\CommandFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming $naming
 */
$commandName = CommandFeature::commandName($naming);
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
## Console Command

The `<?= $commandName ?>` command greets someone with the greeter:

```console
$ php bin/console <?= $commandName ?> Jane

 [OK] <?= $greeting ?> Jane!

$ php bin/console <?= $commandName ?> Jane --yell

 [OK] <?= strtoupper($greeting) ?> JANE!
```

Run `php bin/console <?= $commandName ?> --help` to display its usage, and
`php bin/console list <?= $naming->slug ?>` to list the commands of the bundle.

The command is the `<?= CommandFeature::serviceId($naming) ?>` service. It is loaded lazily:
listing the commands does not create it nor its dependencies.
