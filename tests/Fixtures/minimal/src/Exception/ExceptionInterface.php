<?php

declare(strict_types=1);

namespace Acme\MinimalBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeMinimalBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
