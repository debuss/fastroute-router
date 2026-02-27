<?php

namespace Router;

use Closure;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class MethodNotAllowed implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private  readonly Closure|ResponseInterface|ResponseFactoryInterface $response
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteResult|null $routeResult */
        $routeResult = $request->getAttribute($this->attribute);
        if ($routeResult === null || !$routeResult->isMethodFailure()) {
            return $handler->handle($request);
        }

        if ($this->response instanceof Closure) {
            return call_user_func($this->response, $request, $handler);
        }

        $response = $this->response instanceof ResponseFactoryInterface
            ? $this->response->createResponse(405)
            : $this->response;

        return $response->withHeader('Allow', implode(', ', $routeResult->methods));
    }
}
