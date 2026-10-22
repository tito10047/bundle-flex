<?php

declare(strict_types=1);

namespace Acme\EventListenerBundle\Exception;

/**
 * Thrown when an argument has an invalid value.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
