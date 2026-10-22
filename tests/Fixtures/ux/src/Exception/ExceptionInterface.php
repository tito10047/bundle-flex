<?php

declare(strict_types=1);

namespace Acme\UxBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeUxBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
