<?php

declare(strict_types=1);

namespace Acme\BackendYamlBundle\Exception;

/**
 * Implemented by every exception thrown by AcmeBackendYamlBundle, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
