<?php

declare(strict_types=1);

namespace Acme\EverythingPhpArrayBundle\EventListener;

use Acme\EverythingPhpArrayBundle\GreeterInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds a greeting header to the responses of the application, e.g. "X-Acme-Everything-Php-Array-Greeting: Hello World!".
 *
 * Registered as the "acme_everything_php_array.event_listener.greeting_header" service, listening to the "kernel.response" event.
 */
final readonly class GreetingHeaderListener
{
    public const HEADER = 'X-Acme-Everything-Php-Array-Greeting';

    public function __construct(
        private GreeterInterface $greeter,
    ) {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // the responses of sub-requests (fragments, ESI) are part of the response of the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        // a header set by the application takes precedence
        if ($headers->has(self::HEADER)) {
            return;
        }

        $headers->set(self::HEADER, $this->greeter->greet('World'));
    }
}
