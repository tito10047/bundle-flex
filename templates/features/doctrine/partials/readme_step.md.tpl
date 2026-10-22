<?php
use Yceruto\BundleFlex\Feature\DoctrineFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming $naming
 */
?>
Create the Database Table

The bundle stores its entities in the `<?= DoctrineFeature::tableName($naming) ?>` table. Create it with a
[migration](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html):

```console
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```
