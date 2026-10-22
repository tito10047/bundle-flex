<?php

declare(strict_types=1);

namespace Acme\BackendYamlBundle;

use Acme\BackendYamlBundle\Exception\InvalidArgumentException;

/**
 * Greets people.
 *
 * Type-hint this interface in your services to get the "acme_backend_yaml.greeter" service autowired.
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
