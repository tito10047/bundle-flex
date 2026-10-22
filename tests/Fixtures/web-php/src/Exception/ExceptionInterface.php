<?php

declare(strict_types=1);

namespace Acme\WebPhpBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeWebPhpBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
