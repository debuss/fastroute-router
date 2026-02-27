<?php

use Router\Attribute\{Get, Method};

covers(Get::class);

test('Get attribute creates route with GET method', function () {
    $get = new Get('/test');

    expect($get->path)->toBe('/test')
        ->and($get->methods)->toBe(['GET'])
        ->and($get->name)->toBeNull()
        ->and($get->priority)->toBeNull();
});

test('Get attribute can be created with name', function () {
    $get = new Get('/users', 'users.index');

    expect($get->path)->toBe('/users')
        ->and($get->methods)->toBe(['GET'])
        ->and($get->name)->toBe('users.index');
});

test('Get attribute can be created with priority', function () {
    $get = new Get('/users', null, 10);

    expect($get->path)->toBe('/users')
        ->and($get->methods)->toBe(['GET'])
        ->and($get->priority)->toBe(10);
});

test('Get attribute can be created with name and priority', function () {
    $get = new Get('/users/{id}', 'users.show', 5);

    expect($get->path)->toBe('/users/{id}')
        ->and($get->methods)->toBe(['GET'])
        ->and($get->name)->toBe('users.show')
        ->and($get->priority)->toBe(5);
});

test('Get attribute extends Method', function () {
    $get = new Get('/test');

    expect($get)->toBeInstanceOf(Method::class);
});

test('Get attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Get::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

