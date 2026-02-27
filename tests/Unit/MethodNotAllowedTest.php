<?php

use Router\{MethodNotAllowed, RouteResult};
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

covers(MethodNotAllowed::class);

beforeEach(function () {
    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->responseFactory = Mockery::mock(ResponseFactoryInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('MethodNotAllowed passes through when no route result', function () {
    $middleware = new MethodNotAllowed($this->response);

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn(null);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('MethodNotAllowed passes through when route result is not method failure', function () {
    $middleware = new MethodNotAllowed($this->response);
    $routeResult = RouteResult::fromRouteFailure([]);

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('MethodNotAllowed executes closure when method is not allowed', function () {
    $receivedRequest = null;
    $receivedHandler = null;
    $routeResult = RouteResult::fromRouteFailure(['GET', 'POST']);

    $closure = function ($request, $handler) use (&$receivedRequest, &$receivedHandler) {
        $receivedRequest = $request;
        $receivedHandler = $handler;

        return Mockery::mock(ResponseInterface::class);
    };

    $middleware = new MethodNotAllowed($closure);

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $result = $middleware->process($this->request, $this->handler);

    expect($receivedRequest)->toBe($this->request)
        ->and($receivedHandler)->toBe($this->handler)
        ->and($result)->toBeInstanceOf(ResponseInterface::class);
});

test('MethodNotAllowed creates response from factory and sets Allow header', function () {
    $middleware = new MethodNotAllowed($this->responseFactory);
    $routeResult = RouteResult::fromRouteFailure(['GET', 'POST']);

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->responseFactory->shouldReceive('createResponse')
        ->with(405)
        ->andReturn($this->response);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'GET, POST')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('MethodNotAllowed sets Allow header on provided response', function () {
    $middleware = new MethodNotAllowed($this->response);
    $routeResult = RouteResult::fromRouteFailure(['PUT']);

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'PUT')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('MethodNotAllowed uses custom attribute name when set', function () {
    $middleware = new MethodNotAllowed($this->response);
    $routeResult = RouteResult::fromRouteFailure(['GET']);

    $middleware->setAttribute('route_result');

    $this->request->shouldReceive('getAttribute')
        ->with('route_result')
        ->andReturn($routeResult);

    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'GET')
        ->andReturn($this->response);

    $result = $middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});
