<?php

declare(strict_types=1);

namespace Acme\EventListenerBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeEventListenerBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
