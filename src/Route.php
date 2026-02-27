<?php

namespace Router;

use Closure;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

readonly class Route
{

    /** @var string[] */
    public array $methods;
    public string $path;
    /** @var RequestHandlerInterface|MiddlewareInterface|array<string>|string|Closure|callable */
    public mixed $handler;
    public string $name;
    public int $priority;

    /**
     * @param string[] $methods
     * @param RequestHandlerInterface|MiddlewareInterface|array<string>|string|Closure|callable $handler
     */
    public function __construct(
        array $methods,
        string $path,
        RequestHandlerInterface|MiddlewareInterface|array|string|Closure|callable $handler,
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
