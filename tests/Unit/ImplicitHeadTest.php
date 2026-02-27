<?php

use Router\{ImplicitHead, Route, RouteResult};
use FastRoute\Dispatcher;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface, StreamFactoryInterface, StreamInterface, UriInterface};
use Psr\Http\Server\RequestHandlerInterface;

covers(ImplicitHead::class);

beforeEach(function () {
    $this->dispatcher = Mockery::mock(Dispatcher::class);
    $this->streamFactory = Mockery::mock(StreamFactoryInterface::class);

    $this->middleware = new ImplicitHead($this->dispatcher, $this->streamFactory);

    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->uri = Mockery::mock(UriInterface::class);
    $this->stream = Mockery::mock(StreamInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('ImplicitHead passes through when method is not HEAD', function () {
    $this->request->shouldReceive('getMethod')
        ->andReturn('GET');

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitHead passes through when no route result is present', function () {
    $this->request->shouldReceive('getMethod')
        ->andReturn('HEAD');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn(null);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitHead passes through when route result is successful', function () {
    $route = new Route(['HEAD'], '/health', 'Handler');
    $routeResult = RouteResult::fromRouteSuccess($route, []);

    $this->request->shouldReceive('getMethod')
        ->andReturn('HEAD');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitHead passes through when GET dispatch is not found', function () {
    $routeResult = RouteResult::fromRouteFailure(['GET']);

    $this->request->shouldReceive('getMethod')
        ->andReturn('HEAD');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->request->shouldReceive('getUri')
        ->andReturn($this->uri);

    $this->uri->shouldReceive('getPath')
        ->andReturn('/missing');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/missing')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitHead converts to GET and strips response body when GET route exists', function () {
    $routeResult = RouteResult::fromRouteFailure(['GET']);
    $route = new Route(['GET'], '/users/{id}', 'Handler');
    $params = ['id' => '10'];

    $requestAfterMethod = Mockery::mock(ServerRequestInterface::class);
    $requestWithAttribute = Mockery::mock(ServerRequestInterface::class);

    $this->request->shouldReceive('getMethod')
        ->andReturn('HEAD');

    $this->request->shouldReceive('getAttribute')
        ->with(RouteResult::class)
        ->andReturn($routeResult);

    $this->request->shouldReceive('getUri')
        ->andReturn($this->uri);

    $this->uri->shouldReceive('getPath')
        ->andReturn('/users/10');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/users/10')
        ->andReturn([Dispatcher::FOUND, $route, $params]);

    $this->request->shouldReceive('withMethod')
        ->with('GET')
        ->andReturn($requestAfterMethod);

    $requestAfterMethod->shouldReceive('withAttribute')
        ->with(RouteResult::class, Mockery::on(function ($result) use ($route, $params) {
            return $result instanceof RouteResult
                && $result->success === true
                && $result->route === $route
                && $result->params === $params;
        }))
        ->andReturn($requestWithAttribute);

    $this->handler->shouldReceive('handle')
        ->with($requestWithAttribute)
        ->andReturn($this->response);

    $this->streamFactory->shouldReceive('createStream')
        ->andReturn($this->stream);

    $this->response->shouldReceive('withBody')
        ->with($this->stream)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('ImplicitHead uses custom attribute name when set', function () {
    $routeResult = RouteResult::fromRouteFailure(['GET']);

    $this->middleware->setAttribute('route_result');

    $this->request->shouldReceive('getMethod')
        ->andReturn('HEAD');

    $this->request->shouldReceive('getAttribute')
        ->with('route_result')
        ->andReturn($routeResult);

    $this->request->shouldReceive('getUri')
        ->andReturn($this->uri);

    $this->uri->shouldReceive('getPath')
        ->andReturn('/missing');

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/missing')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->middleware->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

