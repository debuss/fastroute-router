<?php

use Router\FastRouteRouter;
use Router\FastRouteDispatcher;
use Router\Route;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

test('router and dispatcher work together to handle requests', function () {
    // Setup mocks
    $dispatcher = Mockery::mock(Dispatcher::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $container = Mockery::mock(ContainerInterface::class);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    // Create middleware instance
    $testMiddleware = new class implements MiddlewareInterface {
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            $response = Mockery::mock(ResponseInterface::class);
            return $response;
        }
    };

    // Setup router
    $router = new FastRouteRouter($dispatcher, $responseFactory);

    // Setup dispatcher
    $routeDispatcher = new FastRouteDispatcher($container);

    // Configure request
    $uri->shouldReceive('getPath')->andReturn('/users/123');
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getUri')->andReturn($uri);

    // Create route
    $route = new Route(['GET'], '/users/{id}', 'UserController');

    // Configure FastRoute dispatcher
    $dispatcher->shouldReceive('dispatch')
        ->with('GET', '/users/123')
        ->andReturn([Dispatcher::FOUND, $route, ['id' => '123']]);

    // Mock request attribute chain
    $requestWithId = Mockery::mock(ServerRequestInterface::class);
    $requestWithRoute = Mockery::mock(ServerRequestInterface::class);

    $request->shouldReceive('withAttribute')
        ->with('id', '123')
        ->andReturn($requestWithId);

    $requestWithId->shouldReceive('withAttribute')
        ->with(Route::class, [Dispatcher::FOUND, $route, ['id' => '123']])
        ->andReturn($requestWithRoute);

    // Configure second stage (dispatcher processing)
    $requestWithRoute->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $container->shouldReceive('has')
        ->with('UserController')
        ->andReturn(true);

    $container->shouldReceive('get')
        ->with('UserController')
        ->andReturn($testMiddleware);

    // First middleware (router) processes the request
    $handlerMock = Mockery::mock(RequestHandlerInterface::class);
    $handlerMock->shouldReceive('handle')
        ->with($requestWithRoute)
        ->andReturn($response);

    $result = $router->process($request, $handlerMock);

    expect($result)->toBe($response);

    Mockery::close();
});

test('full routing flow with 404 response', function () {
    $dispatcher = Mockery::mock(Dispatcher::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $router = new FastRouteRouter($dispatcher, $responseFactory);

    $uri->shouldReceive('getPath')->andReturn('/non-existent');
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getUri')->andReturn($uri);

    $dispatcher->shouldReceive('dispatch')
        ->with('GET', '/non-existent')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $responseFactory->shouldReceive('createResponse')
        ->with(404)
        ->andReturn($response);

    $result = $router->process($request, $handler);

    expect($result)->toBe($response);

    Mockery::close();
});

test('full routing flow with 405 response', function () {
    $dispatcher = Mockery::mock(Dispatcher::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $router = new FastRouteRouter($dispatcher, $responseFactory);

    $uri->shouldReceive('getPath')->andReturn('/users');
    $request->shouldReceive('getMethod')->andReturn('DELETE');
    $request->shouldReceive('getUri')->andReturn($uri);

    $dispatcher->shouldReceive('dispatch')
        ->with('DELETE', '/users')
        ->andReturn([Dispatcher::METHOD_NOT_ALLOWED, ['GET', 'POST']]);

    $responseWith405 = Mockery::mock(ResponseInterface::class);
    $response->shouldReceive('withHeader')
        ->with('Allow', 'GET, POST')
        ->andReturn($responseWith405);

    $responseFactory->shouldReceive('createResponse')
        ->with(405)
        ->andReturn($response);

    $result = $router->process($request, $handler);

    expect($result)->toBe($responseWith405);

    Mockery::close();
});

test('dispatcher handles callable route handlers', function () {
    $container = Mockery::mock(ContainerInterface::class);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);

    $callable = function (ServerRequestInterface $req, RequestHandlerInterface $hand) use ($response) {
        return $response;
    };

    $route = new Route(['POST'], '/create', $callable);

    $dispatcher = new FastRouteDispatcher($container);

    $request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $result = $dispatcher->process($request, $handler);

    expect($result)->toBe($response);

    Mockery::close();
});

test('custom attribute name works across router and dispatcher', function () {
    $dispatcherMock = Mockery::mock(Dispatcher::class);
    $responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $container = Mockery::mock(ContainerInterface::class);
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $router = new FastRouteRouter($dispatcherMock, $responseFactory);
    $router->setAttribute('custom.route.attribute');

    $routeDispatcher = new FastRouteDispatcher($container);
    $routeDispatcher->setAttribute('custom.route.attribute');

    $uri->shouldReceive('getPath')->andReturn('/test');
    $request->shouldReceive('getMethod')->andReturn('GET');
    $request->shouldReceive('getUri')->andReturn($uri);

    $route = new Route(['GET'], '/test', fn() => $response);

    $dispatcherMock->shouldReceive('dispatch')
        ->with('GET', '/test')
        ->andReturn([Dispatcher::FOUND, $route, []]);

    $requestWithRoute = Mockery::mock(ServerRequestInterface::class);
    $request->shouldReceive('withAttribute')
        ->with('custom.route.attribute', [Dispatcher::FOUND, $route, []])
        ->andReturn($requestWithRoute);

    $handlerMock = Mockery::mock(RequestHandlerInterface::class);
    $handlerMock->shouldReceive('handle')
        ->with($requestWithRoute)
        ->andReturn($response);

    $result = $router->process($request, $handlerMock);

    expect($result)->toBe($response);

    Mockery::close();
});

