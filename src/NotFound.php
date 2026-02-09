<?php

namespace Router;

use Closure;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

readonly class NotFound implements MiddlewareInterface
{

    public function __construct(
        private  Closure|ResponseInterface $response
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->response instanceof ResponseInterface) {
            return $this->response;
        }

        return call_user_func($this->response, $request, $handler);
    }
}
