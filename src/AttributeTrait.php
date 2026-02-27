<?php

namespace Router;

trait AttributeTrait
{

    private string $attribute = RouteResult::class;

    public function getAttribute(): string
    {
        return $this->attribute;
    }

    public function setAttribute(string $attribute): void
    {
        $this->attribute = $attribute;
    }
}
