<?php

declare(strict_types=1);

namespace Acme\ControllersBundle\Controller;

use Acme\ControllersBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Greets people, in plain text.
 *
 * Registered as the "acme_controllers.controller.hello" service, and routed by the "acme_controllers_hello" route.
 */
final readonly class HelloController
{
    public function __construct(
        private GreeterInterface $greeter,
    ) {
    }

    public function __invoke(string $name): Response
    {
        return new Response($this->greeter->greet($name), Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
