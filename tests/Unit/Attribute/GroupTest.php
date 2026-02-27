<?php

use Router\Attribute\Group;

covers(Group::class);

test('Group attribute can be created with path only', function () {
    $group = new Group('/api');

    expect($group->path)->toBe('/api')
        ->and($group->priority)->toBeNull();
});

test('Group attribute can be created with priority', function () {
    $group = new Group('/api', 10);

    expect($group->path)->toBe('/api')
        ->and($group->priority)->toBe(10);
});

test('Group attribute has correct PHP attribute configuration', function () {
    $reflection = new ReflectionClass(Group::class);
    $attributes = $reflection->getAttributes(Attribute::class);

    expect($attributes)->toHaveCount(1);

    $attribute = $attributes[0]->newInstance();
    expect($attribute->flags)->toBe(Attribute::TARGET_CLASS);
});

