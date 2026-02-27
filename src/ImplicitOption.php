<?php

namespace Router;

use Closure;
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class ImplicitOption implements MiddlewareInterface
{

    use AttributeTrait;

    public function __construct(
        private Closure|ResponseInterface|ResponseFactoryInterface $response
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) != 'OPTIONS') {
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

        if (!$result->isMethodFailure()) {
            return $handler->handle($request);
        }

        if ($this->response instanceof Closure) {
            return call_user_func($this->response, $request, $handler);
        }

        $response = $this->response instanceof ResponseFactoryInterface
            ? $this->response->createResponse()
            : $this->response;

        return $response->withHeader('Allow', implode(', ', $result->methods));
    }
}
