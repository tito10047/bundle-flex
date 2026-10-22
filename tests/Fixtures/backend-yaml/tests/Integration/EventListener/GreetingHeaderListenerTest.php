<?php

declare(strict_types=1);

namespace Acme\BackendYamlBundle\Tests\Integration\EventListener;

use Acme\BackendYamlBundle\EventListener\GreetingHeaderListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class GreetingHeaderListenerTest extends KernelTestCase
{
    public function testListenerIsRegistered(): void
    {
        self::bootKernel();
        $dispatcher = $this->getEventDispatcher();

        $registered = [];
        foreach ($dispatcher->getListeners(KernelEvents::RESPONSE) as $listener) {
            if (\is_array($listener) && $listener[0] instanceof GreetingHeaderListener) {
                // Symfony 7.4 documents the listeners as "callable[]|callable": the callable is rebuilt for
                // the static analysis
                $registered[] = [$listener[1], $dispatcher->getListenerPriority(KernelEvents::RESPONSE, [$listener[0], 'onKernelResponse'])];
            }
        }

        // the method and the priority of the "kernel.event_listener" tag
        self::assertSame([['onKernelResponse', 0]], $registered);
    }

    public function testResponsesGetTheGreetingHeader(): void
    {
        $kernel = self::bootKernel();
        $response = new Response();

        $this->getEventDispatcher()->dispatch(
            new ResponseEvent($kernel, Request::create('/'), HttpKernelInterface::MAIN_REQUEST, $response),
            KernelEvents::RESPONSE,
        );

        self::assertSame('Hello World!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    private function getEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = self::getContainer()->get('event_dispatcher');
        self::assertInstanceOf(EventDispatcherInterface::class, $dispatcher);

        return $dispatcher;
    }
}
