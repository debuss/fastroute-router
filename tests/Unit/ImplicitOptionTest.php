<?php

use Router\{ImplicitOption, RouteResult};
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

covers(ImplicitOption::class);

beforeEach(function () {
    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->responseFactory = Mockery::mock(ResponseFactoryInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('ImplicitOption passes through when method is not OPTIONS', function () {
    $middleware = new ImplicitOption($this->response);

    $this->request->shouldReceive('getMethod')
        ->andReturn('GET');

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption passes through when no route result is present', function () {
    $middleware = new ImplicitOption($this->response);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn(null);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption passes through when route result is successful', function () {
    $middleware = new ImplicitOption($this->response);
    $routeResult = RouteResult::fromRouteSuccess(new Router\Route(['OPTIONS'], '/status', 'Handler'), []);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption passes through when route result is not method failure', function () {
    $middleware = new ImplicitOption($this->response);
    $routeResult = RouteResult::fromRouteFailure([]);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption executes closure when method is not allowed', function () {
    $receivedRequest = null;
    $receivedHandler = null;
    $routeResult = RouteResult::fromRouteFailure(['GET', 'POST']);

    $closure = function ($request, $handler) use (&$receivedRequest, &$receivedHandler) {
        $receivedRequest = $request;
        $receivedHandler = $handler;

        return Mockery::mock(ResponseInterface::class);
    };

    $middleware = new ImplicitOption($closure);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $result = $middleware->process($this->request, $this->handler);

    expect($receivedRequest)->toBe($this->request)
        ->and($receivedHandler)->toBe($this->handler)
        ->and($result)->toBeInstanceOf(ResponseInterface::class);
});

test('ImplicitOption creates response from factory and sets Allow header', function () {
    $middleware = new ImplicitOption($this->responseFactory);
    $routeResult = RouteResult::fromRouteFailure(['GET', 'POST']);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->responseFactory->shouldReceive('createResponse')
        ->withNoArgs()
        ->andReturn($this->response);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'GET, POST')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption sets Allow header on provided response', function () {
    $middleware = new ImplicitOption($this->response);
    $routeResult = RouteResult::fromRouteFailure(['PUT']);

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'PUT')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitOption uses custom attribute name when set', function () {
    $middleware = new ImplicitOption($this->response);
    $routeResult = RouteResult::fromRouteFailure(['GET']);

    $middleware->setAttribute('route_result');

    $this->request->shouldReceive('getMethod')
        ->andReturn('OPTIONS');

    $this->request->shouldReceive('getAttribute')
        ->with('route_result')
        ->andReturn($routeResult);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'GET')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

