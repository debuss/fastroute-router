<?php

namespace Router\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
readonly class Any extends Method
{

    public function __construct(string $path, ?string $name = null, ?int $priority = null)
    {
        parent::__construct($path, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'], $name, $priority);
    }
}
