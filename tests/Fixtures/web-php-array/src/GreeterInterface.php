<?php

declare(strict_types=1);

namespace Acme\WebPhpArrayBundle;

use Acme\WebPhpArrayBundle\Exception\InvalidArgumentException;

/**
 * Greets people.
 *
 * Type-hint this interface in your services to get the "acme_web_php_array.greeter" service autowired.
 */
interface GreeterInterface
{
    /**
     * Returns the greeting of the given name, e.g. "Hello Jane!".
     *
     * @throws InvalidArgumentException when the name is empty
     */
    public function greet(string $name): string;
}
