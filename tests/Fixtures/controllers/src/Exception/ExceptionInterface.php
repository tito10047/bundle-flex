<?php

declare(strict_types=1);

namespace Acme\ControllersBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeControllersBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
