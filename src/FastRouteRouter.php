<?php

namespace Router;

use FastRoute\Dispatcher;
use LogicException;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class FastRouteRouter implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route_info = $this->dispatcher->dispatch($request->getMethod(), rawurldecode($request->getUri()->getPath()));

        $result = match ($route_info[0]) {
            Dispatcher::NOT_FOUND => RouteResult::fromRouteFailure([]),
            Dispatcher::METHOD_NOT_ALLOWED => RouteResult::fromRouteFailure($route_info[1]),
            Dispatcher::FOUND => RouteResult::fromRouteSuccess($route_info[1], $route_info[2]),
            default => throw new LogicException('Unknown route dispatch result: ' . $route_info[0]),
        };

        $request = $request->withAttribute($this->attribute, $result);

        return $handler->handle($request);
    }
}
