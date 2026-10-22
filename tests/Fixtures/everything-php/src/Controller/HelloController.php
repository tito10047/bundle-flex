<?php

declare(strict_types=1);

namespace Acme\EverythingPhpBundle\Controller;

use Acme\EverythingPhpBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@AcmeEverythingPhp/hello.html.twig" template.
 *
 * Registered as the "acme_everything_php.controller.hello" service, and routed by the "acme_everything_php_hello" route.
 */
final readonly class HelloController
{
    public function __construct(
        private GreeterInterface $greeter,
        private Environment $twig,
    ) {
    }

    public function __invoke(string $name): Response
    {
        return new Response($this->twig->render('@AcmeEverythingPhp/hello.html.twig', [
            'greeting' => $this->greeter->greet($name),
        ]));
    }
}
