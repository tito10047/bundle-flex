<?php

declare(strict_types=1);

namespace Acme\BackendPhpArrayBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeBackendPhpArrayBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
