<?php

namespace Router\Attribute;

abstract readonly class Method
{

    public function __construct(
        public string $path,
        /** @var string[] */
        public array $methods = [],
        public ?string $name = null,
        public ?int $priority = null
    ) {}
}
