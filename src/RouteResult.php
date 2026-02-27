<?php

namespace Router;

readonly class RouteResult
{

    protected function __construct(
        public ?Route $route,
        /** @var array<string, mixed> */
        public array $params,
        public bool $success,
        /** @var string[]|null */
        public ?array $methods
    ) {}

    /**
     * @param array<string, mixed> $params
     */
    public static function fromRouteSuccess(Route $route, array $params): self
    {
        return new self($route, $params, true, null);
    }

    /**
     * @param string[] $methods
     */
    public static function fromRouteFailure(array $methods): self
    {
        return new self(null, [], false, $methods);
    }

    public function isMethodFailure(): bool
    {
        return !$this->success && is_array($this->methods) && count($this->methods);
    }
}
