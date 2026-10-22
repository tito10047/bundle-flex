<?php

declare(strict_types=1);

namespace Acme\DoctrineEntityBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeDoctrineEntityBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
