<?php

namespace Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
readonly class Group
{

    public function __construct(
        public string $path,
        public ?int $priority = null
    ) {}
}
