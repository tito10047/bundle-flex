<?php

declare(strict_types=1);

namespace Acme\BlogBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeBlogBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
