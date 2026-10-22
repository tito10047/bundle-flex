<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle\Controller;

use MyVendor\EverythingYamlBundle\GreeterInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Greets people on a page rendered from the "@MyVendorEverythingYaml/hello.html.twig" template.
 *
 * Registered as the "my_vendor_everything_yaml.controller.hello" service, and routed by the "my_vendor_everything_yaml_hello" route.
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
        return new Response($this->twig->render('@MyVendorEverythingYaml/hello.html.twig', [
            'greeting' => $this->greeter->greet($name),
        ]));
    }
}
