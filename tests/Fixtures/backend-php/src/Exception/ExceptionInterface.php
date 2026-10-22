<?php

declare(strict_types=1);

namespace Acme\BackendPhpBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeBackendPhpBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
