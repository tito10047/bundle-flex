<?php

declare(strict_types=1);

namespace Acme\PhpArrayKitchenSinkBundle\Exception;

/**
 * Thrown when an argument has an invalid value.
 */
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}
