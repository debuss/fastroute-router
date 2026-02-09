<?php

namespace Router;

use FastRoute\Dispatcher;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class FastRouteRouter implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private Dispatcher $router,
        private ResponseFactoryInterface $responseFactory
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->dispatch($request->getMethod(), rawurldecode($request->getUri()->getPath()));

        if ($route[0] === Dispatcher::NOT_FOUND) {
            return $this->responseFactory->createResponse(404);
        }

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            return $this->responseFactory->createResponse(405)->withHeader('Allow', implode(', ', $route[1]));
        }

        foreach ($route[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute($this->attribute, $route[1]);

        return $handler->handle($request);
    }
}
