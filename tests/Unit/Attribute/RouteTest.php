<?php

use Router\Attribute\Route;

test('Route attribute can be created with path only', function () {
    $route = new Route('/test');

    expect($route->path)->toBe('/test')
        ->and($route->methods)->toBe(['GET'])
        ->and($route->name)->toBeNull()
        ->and($route->priority)->toBeNull();
});

test('Route attribute can be created with custom methods', function () {
    $route = new Route('/test', ['POST', 'PUT']);

    expect($route->path)->toBe('/test')
        ->and($route->methods)->toBe(['POST', 'PUT']);
});

test('Route attribute can be created with name', function () {
    $route = new Route('/test', ['GET'], 'test.route');

    expect($route->name)->toBe('test.route');
});

test('Route attribute can be created with priority', function () {
    $route = new Route('/test', ['GET'], null, 10);

    expect($route->priority)->toBe(10);
});

test('Route attribute can be created with all parameters', function () {
    $route = new Route('/users/{id}', ['GET', 'POST'], 'users.show', 5);

    expect($route->path)->toBe('/users/{id}')
        ->and($route->methods)->toBe(['GET', 'POST'])
        ->and($route->name)->toBe('users.show')
        ->and($route->priority)->toBe(5);
});

test('Route attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Route::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

