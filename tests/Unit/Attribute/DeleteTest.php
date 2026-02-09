<?php

use Router\Attribute\Delete;
use Router\Attribute\Route;

test('Delete attribute creates route with DELETE method', function () {
    $delete = new Delete('/test');

    expect($delete->path)->toBe('/test')
        ->and($delete->methods)->toBe(['DELETE'])
        ->and($delete->name)->toBeNull()
        ->and($delete->priority)->toBeNull();
});

test('Delete attribute can be created with name', function () {
    $delete = new Delete('/users/{id}', 'users.destroy');

    expect($delete->path)->toBe('/users/{id}')
        ->and($delete->methods)->toBe(['DELETE'])
        ->and($delete->name)->toBe('users.destroy');
});

test('Delete attribute can be created with priority', function () {
    $delete = new Delete('/users/{id}', null, 10);

    expect($delete->path)->toBe('/users/{id}')
        ->and($delete->methods)->toBe(['DELETE'])
        ->and($delete->priority)->toBe(10);
});

test('Delete attribute can be created with name and priority', function () {
    $delete = new Delete('/users/{id}', 'users.destroy', 5);

    expect($delete->path)->toBe('/users/{id}')
        ->and($delete->methods)->toBe(['DELETE'])
        ->and($delete->name)->toBe('users.destroy')
        ->and($delete->priority)->toBe(5);
});

test('Delete attribute extends Route', function () {
    $delete = new Delete('/test');

    expect($delete)->toBeInstanceOf(Route::class);
});


test('Delete attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Delete::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

