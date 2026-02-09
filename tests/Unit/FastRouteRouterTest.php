<?php

use Router\FastRouteRouter;
use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;

beforeEach(function () {
    $this->dispatcher = Mockery::mock(Dispatcher::class);
    $this->responseFactory = Mockery::mock(ResponseFactoryInterface::class);
    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
    $this->uri = Mockery::mock(UriInterface::class);

    $this->router = new FastRouteRouter($this->dispatcher, $this->responseFactory);
});

afterEach(function () {
    Mockery::close();
});

test('FastRouteRouter returns 404 response when route not found', function () {
    $this->uri->shouldReceive('getPath')->andReturn('/not-found');
    $this->request->shouldReceive('getMethod')->andReturn('GET');
    $this->request->shouldReceive('getUri')->andReturn($this->uri);

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/not-found')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $this->responseFactory->shouldReceive('createResponse')
        ->with(404)
        ->andReturn($this->response);

    $result = $this->router->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteRouter returns 405 response when method not allowed', function () {
    $this->uri->shouldReceive('getPath')->andReturn('/test');
    $this->request->shouldReceive('getMethod')->andReturn('POST');
    $this->request->shouldReceive('getUri')->andReturn($this->uri);

    $allowedMethods = ['GET', 'PUT'];
    $this->dispatcher->shouldReceive('dispatch')
        ->with('POST', '/test')
        ->andReturn([Dispatcher::METHOD_NOT_ALLOWED, $allowedMethods]);

    $responseWith405 = Mockery::mock(ResponseInterface::class);
    $this->response->shouldReceive('withHeader')
        ->with('Allow', 'GET, PUT')
        ->andReturn($responseWith405);

    $this->responseFactory->shouldReceive('createResponse')
        ->with(405)
        ->andReturn($this->response);

    $result = $this->router->process($this->request, $this->handler);

    expect($result)->toBe($responseWith405);
});

test('FastRouteRouter dispatches found route with parameters', function () {
    $this->uri->shouldReceive('getPath')->andReturn('/users/123');
    $this->request->shouldReceive('getMethod')->andReturn('GET');
    $this->request->shouldReceive('getUri')->andReturn($this->uri);

    $routeInfo = ['handler' => 'UserController'];
    $params = ['id' => '123'];

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/users/123')
        ->andReturn([Dispatcher::FOUND, $routeInfo, $params]);

    $requestWithId = Mockery::mock(ServerRequestInterface::class);
    $requestWithRoute = Mockery::mock(ServerRequestInterface::class);

    $this->request->shouldReceive('withAttribute')
        ->with('id', '123')
        ->andReturn($requestWithId);

    $requestWithId->shouldReceive('withAttribute')
        ->with(Router\Route::class, [Dispatcher::FOUND, $routeInfo, $params])
        ->andReturn($requestWithRoute);

    $this->handler->shouldReceive('handle')
        ->with($requestWithRoute)
        ->andReturn($this->response);

    $result = $this->router->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteRouter handles URL encoded paths', function () {
    $this->uri->shouldReceive('getPath')->andReturn('/users/john%20doe');
    $this->request->shouldReceive('getMethod')->andReturn('GET');
    $this->request->shouldReceive('getUri')->andReturn($this->uri);

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/users/john doe')
        ->andReturn([Dispatcher::NOT_FOUND]);

    $this->responseFactory->shouldReceive('createResponse')
        ->with(404)
        ->andReturn($this->response);

    $result = $this->router->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('FastRouteRouter can get attribute name', function () {
    expect($this->router->getAttribute())->toBe(Router\Route::class);
});

test('FastRouteRouter can set custom attribute name', function () {
    $this->router->setAttribute('custom.attribute');

    expect($this->router->getAttribute())->toBe('custom.attribute');
});

test('FastRouteRouter adds multiple route parameters to request', function () {
    $this->uri->shouldReceive('getPath')->andReturn('/posts/123/comments/456');
    $this->request->shouldReceive('getMethod')->andReturn('GET');
    $this->request->shouldReceive('getUri')->andReturn($this->uri);

    $routeInfo = ['handler' => 'CommentController'];
    $params = ['postId' => '123', 'commentId' => '456'];

    $this->dispatcher->shouldReceive('dispatch')
        ->with('GET', '/posts/123/comments/456')
        ->andReturn([Dispatcher::FOUND, $routeInfo, $params]);

    $requestWithPostId = Mockery::mock(ServerRequestInterface::class);
    $requestWithCommentId = Mockery::mock(ServerRequestInterface::class);
    $requestWithRoute = Mockery::mock(ServerRequestInterface::class);

    $this->request->shouldReceive('withAttribute')
        ->with('postId', '123')
        ->andReturn($requestWithPostId);

    $requestWithPostId->shouldReceive('withAttribute')
        ->with('commentId', '456')
        ->andReturn($requestWithCommentId);

    $requestWithCommentId->shouldReceive('withAttribute')
        ->with(Router\Route::class, [Dispatcher::FOUND, $routeInfo, $params])
        ->andReturn($requestWithRoute);

    $this->handler->shouldReceive('handle')
        ->with($requestWithRoute)
        ->andReturn($this->response);

    $result = $this->router->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

