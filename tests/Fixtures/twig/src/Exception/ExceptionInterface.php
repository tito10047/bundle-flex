<?php

declare(strict_types=1);

namespace Acme\TwigBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeTwigBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
