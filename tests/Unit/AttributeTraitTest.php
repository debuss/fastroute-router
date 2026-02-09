<?php

use Router\AttributeTrait;
use Router\Route;

test('AttributeTrait has default attribute value', function () {
    $class = new class {
        use AttributeTrait;

        public function getAttributeValue(): string {
            return $this->attribute;
        }
    };

    expect($class->getAttributeValue())->toBe(Route::class);
});

test('AttributeTrait can get attribute', function () {
    $class = new class {
        use AttributeTrait;
    };

    expect($class->getAttribute())->toBe(Route::class);
});

test('AttributeTrait can set attribute', function () {
    $class = new class {
        use AttributeTrait;
    };

    $class->setAttribute('custom.attribute');

    expect($class->getAttribute())->toBe('custom.attribute');
});

test('AttributeTrait can set and get multiple times', function () {
    $class = new class {
        use AttributeTrait;
    };

    $class->setAttribute('first.attribute');
    expect($class->getAttribute())->toBe('first.attribute');

    $class->setAttribute('second.attribute');
    expect($class->getAttribute())->toBe('second.attribute');

    $class->setAttribute(Route::class);
    expect($class->getAttribute())->toBe(Route::class);
});

