<?php

declare(strict_types=1);

namespace Acme\CiBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeCiBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
