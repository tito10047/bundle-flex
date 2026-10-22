<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle\Tests\EventListener;

use MyVendor\EverythingYamlBundle\EventListener\GreetingHeaderListener;
use MyVendor\EverythingYamlBundle\Greeter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class GreetingHeaderListenerTest extends TestCase
{
    public function testAddsTheGreetingHeaderToTheMainResponse(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response());

        self::assertSame('Hi World!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    public function testIgnoresTheResponsesOfSubRequests(): void
    {
        $response = $this->dispatch(HttpKernelInterface::SUB_REQUEST, new Response());

        self::assertFalse($response->headers->has(GreetingHeaderListener::HEADER));
    }

    public function testKeepsTheHeaderSetByTheApplication(): void
    {
        $response = $this->dispatch(HttpKernelInterface::MAIN_REQUEST, new Response(headers: [GreetingHeaderListener::HEADER => 'Welcome!']));

        self::assertSame('Welcome!', $response->headers->get(GreetingHeaderListener::HEADER));
    }

    /**
     * Calls the listener with the response of a request of the given type, then returns the response.
     *
     * @param HttpKernelInterface::*_REQUEST $requestType
     */
    private function dispatch(int $requestType, Response $response): Response
    {
        $listener = new GreetingHeaderListener(new Greeter('Hi'));

        $listener->onKernelResponse(new ResponseEvent(self::createStub(HttpKernelInterface::class), Request::create('/'), $requestType, $response));

        return $response;
    }
}
