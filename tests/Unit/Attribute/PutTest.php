<?php

use Router\Attribute\{Put, Method};

covers(Put::class);

test('Put attribute creates route with PUT method', function () {
    $put = new Put('/test');

    expect($put->path)->toBe('/test')
        ->and($put->methods)->toBe(['PUT'])
        ->and($put->name)->toBeNull()
        ->and($put->priority)->toBeNull();
});

test('Put attribute can be created with name', function () {
    $put = new Put('/users/{id}', 'users.update');

    expect($put->path)->toBe('/users/{id}')
        ->and($put->methods)->toBe(['PUT'])
        ->and($put->name)->toBe('users.update');
});

test('Put attribute can be created with priority', function () {
    $put = new Put('/users/{id}', null, 10);

    expect($put->path)->toBe('/users/{id}')
        ->and($put->methods)->toBe(['PUT'])
        ->and($put->priority)->toBe(10);
});

test('Put attribute can be created with name and priority', function () {
    $put = new Put('/users/{id}', 'users.update', 5);

    expect($put->path)->toBe('/users/{id}')
        ->and($put->methods)->toBe(['PUT'])
        ->and($put->name)->toBe('users.update')
        ->and($put->priority)->toBe(5);
});

test('Put attribute extends Method', function () {
    $put = new Put('/test');

    expect($put)->toBeInstanceOf(Method::class);
});

test('Put attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Put::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

