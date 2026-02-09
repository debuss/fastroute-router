<?php

use Router\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

test('Route can be created with basic parameters', function () {
    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: 'TestHandler'
    );

    expect($route->methods)->toBe(['GET'])
        ->and($route->path)->toBe('/test')
        ->and($route->handler)->toBe('TestHandler')
        ->and($route->priority)->toBe(0);
});

test('Route generates default name from methods and path', function () {
    $route = new Route(
        methods: ['GET', 'POST'],
        path: '/users/{id}',
        handler: 'UserHandler'
    );

    expect($route->name)->toBe('GET:POST^/users/{id}');
});

test('Route accepts custom name', function () {
    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: 'TestHandler',
        name: 'test.route'
    );

    expect($route->name)->toBe('test.route');
});

test('Route accepts custom priority', function () {
    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: 'TestHandler',
        priority: 10
    );

    expect($route->priority)->toBe(10);
});

test('Route can accept middleware as handler', function () {
    $middleware = new class implements MiddlewareInterface {
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            return $handler->handle($request);
        }
    };

    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: $middleware
    );

    expect($route->handler)->toBeInstanceOf(MiddlewareInterface::class);
});

test('Route can accept request handler as handler', function () {
    $handler = new class implements RequestHandlerInterface {
        public function handle(ServerRequestInterface $request): ResponseInterface {
            // Mock implementation
            return Mockery::mock(ResponseInterface::class);
        }
    };

    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: $handler
    );

    expect($route->handler)->toBeInstanceOf(RequestHandlerInterface::class);
});

test('Route can accept array as handler', function () {
    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: ['Controller', 'method']
    );

    expect($route->handler)->toBe(['Controller', 'method']);
});

test('Route can accept string as handler', function () {
    $route = new Route(
        methods: ['POST'],
        path: '/api/users',
        handler: 'UserController'
    );

    expect($route->handler)->toBe('UserController');
});

test('Route is readonly', function () {
    $route = new Route(
        methods: ['GET'],
        path: '/test',
        handler: 'TestHandler'
    );

    $reflection = new ReflectionClass(Route::class);
    expect($route)->toBeInstanceOf(Route::class)
        ->and($reflection->isReadOnly())->toBeTrue();
});

test('Route with multiple methods', function () {
    $route = new Route(
        methods: ['GET', 'POST', 'PUT', 'DELETE'],
        path: '/resource',
        handler: 'ResourceHandler'
    );

    expect($route->methods)->toBe(['GET', 'POST', 'PUT', 'DELETE'])
        ->and($route->name)->toBe('GET:POST:PUT:DELETE^/resource');
});

