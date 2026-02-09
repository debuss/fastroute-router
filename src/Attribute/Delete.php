<?php

namespace Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
readonly class Delete extends Route
{

    public function __construct(string $path, ?string $name = null, ?int $priority = null)
    {
        parent::__construct($path, ['DELETE'], $name, $priority);
    }
}
