<?php

namespace Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
readonly class Route
{

    public function __construct(
        public string $path,
        /** @var string[] */
        public array $methods = ['GET'],
        public ?string $name = null,
        public ?int $priority = null
    ) {}
}
