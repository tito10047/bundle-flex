<?php

declare(strict_types=1);

namespace Acme\EverythingPhpArrayBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeEverythingPhpArrayBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
