<?php

use Router\FastRouteDispatcher;
use Router\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

beforeEach(function () {
    $this->container = Mockery::mock(ContainerInterface::class);
    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);

    $this->dispatcher = new FastRouteDispatcher($this->container);
});

afterEach(function () {
    Mockery::close();
});

test('FastRouteDispatcher passes through when no route attribute', function () {
    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn(null);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles middleware from container', function () {
    $middleware = Mockery::mock(MiddlewareInterface::class);

    $route = new Route(['GET'], '/test', 'TestMiddleware');

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->container->shouldReceive('has')
        ->with('TestMiddleware')
        ->andReturn(true);

    $this->container->shouldReceive('get')
        ->with('TestMiddleware')
        ->andReturn($middleware);

    $middleware->shouldReceive('process')
        ->with($this->request, $this->handler)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles request handler from container', function () {
    $requestHandler = Mockery::mock(RequestHandlerInterface::class);

    $route = new Route(['GET'], '/test', 'TestHandler');

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->container->shouldReceive('has')
        ->with('TestHandler')
        ->andReturn(true);

    $this->container->shouldReceive('get')
        ->with('TestHandler')
        ->andReturn($requestHandler);

    $requestHandler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles callable', function () {
    $callable = function ($request, $handler) {
        return $handler->handle($request);
    };

    $route = new Route(['GET'], '/test', $callable);

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles array handler from container', function () {
    $controller = new class {
        public function method($request, $handler) {
            return $handler->handle($request);
        }
    };

    $route = new Route(['GET'], '/test', [get_class($controller), 'method']);

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->container->shouldReceive('get')
        ->with(get_class($controller))
        ->andReturn($controller);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles middleware instance directly', function () {
    $middleware = new class implements MiddlewareInterface {
        public function process(
            ServerRequestInterface $request,
            RequestHandlerInterface $handler
        ): ResponseInterface {
            return $handler->handle($request);
        }
    };

    $route = new Route(['GET'], '/test', $middleware);

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher handles request handler instance directly', function () {
    $requestHandler = new class implements RequestHandlerInterface {
        public ResponseInterface $response;

        public function handle(ServerRequestInterface $request): ResponseInterface {
            return $this->response;
        }
    };

    $requestHandler->response = $this->response;

    $route = new Route(['GET'], '/test', $requestHandler);

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $result = $this->dispatcher->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteDispatcher throws exception for invalid handler', function () {
    $route = new Route(['GET'], '/test', new stdClass());

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->dispatcher->process($this->request, $this->handler);
})->throws(\TypeError::class);

test('FastRouteDispatcher can get attribute name', function () {
    expect($this->dispatcher->getAttribute())->toBe(Route::class);
});

test('FastRouteDispatcher can set custom attribute name', function () {
    $this->dispatcher->setAttribute('custom.route');

    expect($this->dispatcher->getAttribute())->toBe('custom.route');
});

test('FastRouteDispatcher handles string handler not in container', function () {
    $route = new Route(['GET'], '/test', 'NonExistentHandler');

    $this->request->shouldReceive('getAttribute')
        ->with(Route::class)
        ->andReturn($route);

    $this->container->shouldReceive('has')
        ->with('NonExistentHandler')
        ->andReturn(false);

    $this->dispatcher->process($this->request, $this->handler);
})->throws(
    \RuntimeException::class,
    'Route handler is not callable, instance of MiddlewareInterface or RequestHandlerInterface, got string'
);

