<?php

declare(strict_types=1);

namespace Acme\CommandBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeCommandBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
