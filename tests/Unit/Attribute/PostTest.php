<?php

use Router\Attribute\{Post, Method};

covers(Post::class);

test('Post attribute creates route with POST method', function () {
    $post = new Post('/test');

    expect($post->path)->toBe('/test')
        ->and($post->methods)->toBe(['POST'])
        ->and($post->name)->toBeNull()
        ->and($post->priority)->toBeNull();
});

test('Post attribute can be created with name', function () {
    $post = new Post('/users', 'users.store');

    expect($post->path)->toBe('/users')
        ->and($post->methods)->toBe(['POST'])
        ->and($post->name)->toBe('users.store');
});

test('Post attribute can be created with priority', function () {
    $post = new Post('/users', null, 10);

    expect($post->path)->toBe('/users')
        ->and($post->methods)->toBe(['POST'])
        ->and($post->priority)->toBe(10);
});

test('Post attribute can be created with name and priority', function () {
    $post = new Post('/users', 'users.store', 5);

    expect($post->path)->toBe('/users')
        ->and($post->methods)->toBe(['POST'])
        ->and($post->name)->toBe('users.store')
        ->and($post->priority)->toBe(5);
});

test('Post attribute extends Method', function () {
    $post = new Post('/test');

    expect($post)->toBeInstanceOf(Method::class);
});

test('Post attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Post::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE);
});

