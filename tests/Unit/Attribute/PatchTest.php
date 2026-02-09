<?php

use Router\Attribute\Patch;
use Router\Attribute\Route;

test('Patch attribute creates route with PATCH method', function () {
    $patch = new Patch('/test');

    expect($patch->path)->toBe('/test')
        ->and($patch->methods)->toBe(['PATCH'])
        ->and($patch->name)->toBeNull()
        ->and($patch->priority)->toBeNull();
});

test('Patch attribute can be created with name', function () {
    $patch = new Patch('/users/{id}', 'users.patch');

    expect($patch->path)->toBe('/users/{id}')
        ->and($patch->methods)->toBe(['PATCH'])
        ->and($patch->name)->toBe('users.patch');
});

test('Patch attribute can be created with priority', function () {
    $patch = new Patch('/users/{id}', null, 10);

    expect($patch->path)->toBe('/users/{id}')
        ->and($patch->methods)->toBe(['PATCH'])
        ->and($patch->priority)->toBe(10);
});

test('Patch attribute can be created with name and priority', function () {
    $patch = new Patch('/users/{id}', 'users.patch', 5);

    expect($patch->path)->toBe('/users/{id}')
        ->and($patch->methods)->toBe(['PATCH'])
        ->and($patch->name)->toBe('users.patch')
        ->and($patch->priority)->toBe(5);
});

test('Patch attribute extends Route', function () {
    $patch = new Patch('/test');

    expect($patch)->toBeInstanceOf(Route::class);
});

test('Patch attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Patch::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

