<?php

use Router\{Route, RouteResult};

covers(RouteResult::class);

test('RouteResult::fromRouteSuccess sets expected properties', function () {
    $route = new Route(['GET'], '/users/{id}', 'Handler');
    $params = ['id' => '10'];

    $result = RouteResult::fromRouteSuccess($route, $params);

    expect($result->success)->toBeTrue()
        ->and($result->route)->toBe($route)
        ->and($result->params)->toBe($params)
        ->and($result->methods)->toBeNull();
});

test('RouteResult::fromRouteFailure sets expected properties', function () {
    $methods = ['GET', 'POST'];

    $result = RouteResult::fromRouteFailure($methods);

    expect($result->success)->toBeFalse()
        ->and($result->route)->toBeNull()
        ->and($result->params)->toBe([])
        ->and($result->methods)->toBe($methods);
});

test('RouteResult::isMethodFailure returns true when methods are provided', function () {
    $result = RouteResult::fromRouteFailure(['PUT']);

    expect($result->isMethodFailure())->toBeTrue();
});

test('RouteResult::isMethodFailure returns false when methods are empty', function () {
    $result = RouteResult::fromRouteFailure([]);

    expect($result->isMethodFailure())->toBeFalse();
});

test('RouteResult::isMethodFailure returns false for successful result', function () {
    $route = new Route(['GET'], '/health', 'Handler');

    $result = RouteResult::fromRouteSuccess($route, []);

    expect($result->isMethodFailure())->toBeFalse();
});
