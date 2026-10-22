<?php

declare(strict_types=1);

namespace Acme\PhpArrayKitchenSinkBundle\Exception;

/**
 * Implemented by every exception thrown by AcmePhpArrayKitchenSinkBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
