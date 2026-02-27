<?php

use Router\Attribute\{Any, Method};

covers(Any::class);

test('Any attribute creates route with all common HTTP methods', function () {
    $any = new Any('/test');

    expect($any->path)->toBe('/test')
        ->and($any->methods)->toBe(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])
        ->and($any->name)->toBeNull()
        ->and($any->priority)->toBeNull();
});

test('Any attribute can be created with name', function () {
    $any = new Any('/api', 'api.any');

    expect($any->path)->toBe('/api')
        ->and($any->methods)->toBe(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])
        ->and($any->name)->toBe('api.any');
});

test('Any attribute can be created with priority', function () {
    $any = new Any('/api', null, 10);

    expect($any->path)->toBe('/api')
        ->and($any->methods)->toBe(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])
        ->and($any->priority)->toBe(10);
});

test('Any attribute can be created with name and priority', function () {
    $any = new Any('/api', 'api.any', 5);

    expect($any->path)->toBe('/api')
        ->and($any->methods)->toBe(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'])
        ->and($any->name)->toBe('api.any')
        ->and($any->priority)->toBe(5);
});

test('Any attribute extends Method', function () {
    $any = new Any('/test');

    expect($any)->toBeInstanceOf(Method::class);
});

test('Any attribute is readonly', function () {
    $any = new Any('/test');

    $reflection = new ReflectionClass(Any::class);
    expect($any)->toBeInstanceOf(Any::class)
        ->and($reflection->isReadOnly())->toBeTrue();
});

test('Any attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Any::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

