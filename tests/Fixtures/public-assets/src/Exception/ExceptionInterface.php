<?php

declare(strict_types=1);

namespace Acme\PublicAssetsBundle\Exception;

/**
 * Implemented by every exception thrown by AcmePublicAssetsBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
