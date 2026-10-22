<?php

declare(strict_types=1);

namespace Acme\WebPhpArrayBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeWebPhpArrayBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
