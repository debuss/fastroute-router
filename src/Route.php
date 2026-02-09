<?php

namespace Router;

use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

readonly class Route
{

    /** @var string[] */
    public array $methods;
    public string $path;
    /** @var RequestHandlerInterface|MiddlewareInterface|array|string|\Closure|callable */
    public RequestHandlerInterface|MiddlewareInterface|array|string|\Closure $handler;
    public string $name;
    public int $priority;

    public function __construct(
        array $methods,
        string $path,
        RequestHandlerInterface|MiddlewareInterface|array|string|callable $handler,
        ?string $name = null,
        ?int $priority = null
    ) {
        $this->methods = $methods;
        $this->path = $path;
        $this->handler = $handler;
        $this->name = $name ?: sprintf(
            '%s^%s',
            implode(':', $this->methods),
            $this->path
        );
        $this->priority = $priority ?: 0;
    }
}
