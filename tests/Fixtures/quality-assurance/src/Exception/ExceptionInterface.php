<?php

declare(strict_types=1);

namespace Acme\QualityAssuranceBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeQualityAssuranceBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
