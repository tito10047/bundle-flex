<?php

declare(strict_types=1);

namespace Acme\PhpKitchenSinkBundle\Exception;

/**
 * Implemented by every exception thrown by AcmePhpKitchenSinkBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
