<?php

namespace Router;

use FastRoute\Dispatcher;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface, StreamFactoryInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class ImplicitHead implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private readonly Dispatcher $router,
        private readonly StreamFactoryInterface $streamFactory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) != 'HEAD') {
            return $handler->handle($request);
        }

        /** @var RouteResult $result */
        $result = $request->getAttribute($this->attribute);
        if ($result == null) {
            return $handler->handle($request);
        }

        if ($result->success) {
            return $handler->handle($request);
        }

        $route_info = $this->router->dispatch('GET', rawurldecode($request->getUri()->getPath()));
        if ($route_info[0] != Dispatcher::FOUND) {
            return $handler->handle($request);
        }

        $request = $request
            ->withMethod('GET')
            ->withAttribute(
                $this->attribute,
                RouteResult::fromRouteSuccess($route_info[1], $route_info[2])
            );

        $response = $handler->handle($request);

        return $response->withBody($this->streamFactory->createStream());
    }
}
