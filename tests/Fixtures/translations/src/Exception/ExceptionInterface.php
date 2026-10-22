<?php

declare(strict_types=1);

namespace Acme\TranslationsBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeTranslationsBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
