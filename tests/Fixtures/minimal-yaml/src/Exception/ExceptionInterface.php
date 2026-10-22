<?php

declare(strict_types=1);

namespace Acme\MinimalYamlBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeMinimalYamlBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
