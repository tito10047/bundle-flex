<?php

declare(strict_types=1);

namespace Acme\WebPhpArrayBundle\Controller;

use Acme\WebPhpArrayBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@AcmeWebPhpArray/hello.html.twig" template.
 *
 * Registered as the "acme_web_php_array.controller.hello" service, and routed by the "acme_web_php_array_hello" route.
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
        return new Response($this->twig->render('@AcmeWebPhpArray/hello.html.twig', [
            'greeting' => $this->greeter->greet($name),
        ]));
    }
}
