<?php

namespace Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use RuntimeException;

class FastRouteDispatcher implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private ContainerInterface $container
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Route $route */
        $route = $request->getAttribute($this->attribute);
        if ($route === null) {
            return $handler->handle($request);
        }

        $routeHandler = $route->handler;

        if (is_string($routeHandler) && $this->container->has($routeHandler)) {
            $routeHandler = $this->container->get($routeHandler);
        } elseif (is_array($routeHandler) && count($routeHandler) === 2 && is_string($routeHandler[0])) {
            $routeHandler = [$this->container->get($routeHandler[0]), $routeHandler[1]];
        }

        if ($routeHandler instanceof MiddlewareInterface) {
            return $routeHandler->process($request, $handler);
        }

        if ($routeHandler instanceof RequestHandlerInterface) {
            return $routeHandler->handle($request);
        }

        if (is_callable($routeHandler)) {
            return call_user_func_array($routeHandler, [$request, $handler]);
        }

        throw new RuntimeException(sprintf(
            'Route handler is not callable, instance of MiddlewareInterface or RequestHandlerInterface, got %s',
            is_object($routeHandler) ? get_class($routeHandler) : gettype($routeHandler)
        ));
    }
}
