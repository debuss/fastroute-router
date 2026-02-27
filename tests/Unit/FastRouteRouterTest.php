<?php

use Router\{FastRouteRouter, Route, RouteResult};
use FastRoute\Dispatcher;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface, UriInterface};
use Psr\Http\Server\RequestHandlerInterface;

covers(FastRouteRouter::class);

beforeEach(function () {
    $this->dispatcher = Mockery::mock(Dispatcher::class);

    $this->router = new FastRouteRouter($this->dispatcher);
});

afterEach(function () {
    Mockery::close();
});

test('FastRouteRouter attaches not found RouteResult and delegates', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $request->shouldReceive('getMethod')
        ->andReturn('GET');

    $request->shouldReceive('getUri')
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->andReturn('/missing');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/missing')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $request->shouldReceive('withAttribute')
        ->with(RouteResult::class, Mockery::on(function ($result) {
            return $result instanceof RouteResult
                && $result->success === false
                && $result->route === null
                && $result->methods === [];
        }))
        ->andReturn($request);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $this->router->process($request, $handler);

    expect($result)->toBe($response);
});

test('FastRouteRouter attaches method not allowed RouteResult and delegates', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $request->shouldReceive('getMethod')
        ->andReturn('DELETE');

    $request->shouldReceive('getUri')
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->andReturn('/resource');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('DELETE', '/resource')
        ->andReturn([Dispatcher::METHOD_NOT_ALLOWED, ['GET', 'POST']]);

    $request->shouldReceive('withAttribute')
        ->with(RouteResult::class, Mockery::on(function ($result) {
            return $result instanceof RouteResult
                && $result->success === false
                && $result->route === null
                && $result->methods === ['GET', 'POST'];
        }))
        ->andReturn($request);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $this->router->process($request, $handler);

    expect($result)->toBe($response);
});

test('FastRouteRouter attaches found RouteResult and delegates', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $route = new Route(['GET'], '/users/{id}', 'Handler');
    $params = ['id' => '10'];

    $request->shouldReceive('getMethod')
        ->andReturn('GET');

    $request->shouldReceive('getUri')
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->andReturn('/users/10');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/users/10')
        ->andReturn([Dispatcher::FOUND, $route, $params]);

    $request->shouldReceive('withAttribute')
        ->with(RouteResult::class, Mockery::on(function ($result) use ($route, $params) {
            return $result instanceof RouteResult
                && $result->success === true
                && $result->route === $route
                && $result->params === $params
                && $result->methods === null;
        }))
        ->andReturn($request);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $this->router->process($request, $handler);

    expect($result)->toBe($response);
});

test('FastRouteRouter dispatches with decoded URI path', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $request->shouldReceive('getMethod')
        ->andReturn('GET');

    $request->shouldReceive('getUri')
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->andReturn('/file%20name');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/file name')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $request->shouldReceive('withAttribute')
        ->with(RouteResult::class, Mockery::type(RouteResult::class))
        ->andReturn($request);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $this->router->process($request, $handler);

    expect($result)->toBe($response);
});

test('FastRouteRouter uses custom attribute name when set', function () {
    $request = Mockery::mock(ServerRequestInterface::class);
    $handler = Mockery::mock(RequestHandlerInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $uri = Mockery::mock(UriInterface::class);

    $this->router->setAttribute('route_result');

    $request->shouldReceive('getMethod')
        ->andReturn('GET');

    $request->shouldReceive('getUri')
        ->andReturn($uri);

    $uri->shouldReceive('getPath')
        ->andReturn('/custom');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/custom')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $request->shouldReceive('withAttribute')
        ->with('route_result', Mockery::type(RouteResult::class))
        ->andReturn($request);

    $handler->shouldReceive('handle')
        ->with($request)
        ->andReturn($response);

    $result = $this->router->process($request, $handler);

    expect($result)->toBe($response);
});

