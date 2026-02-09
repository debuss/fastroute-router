<?php

use Router\NotFound;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

beforeEach(function () {
    $this->request = Mockery::mock(ServerRequestInterface::class);
    $this->handler = Mockery::mock(RequestHandlerInterface::class);
    $this->response = Mockery::mock(ResponseInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('NotFound returns response when response instance is provided', function () {
    $notFound = new NotFound($this->response);

    $result = $notFound->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('NotFound executes closure when closure is provided', function () {
    $closure = function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        return $handler->handle($request);
    };

    $notFound = new NotFound($closure);

    $this->handler->shouldReceive('handle')
        ->with($this->request)
        ->andReturn($this->response);

    $result = $notFound->process($this->request, $this->handler);

    expect($result)->toBe($this->response);
});

test('NotFound closure receives correct parameters', function () {
    $receivedRequest = null;
    $receivedHandler = null;

    $closure = function ($request, $handler) use (&$receivedRequest, &$receivedHandler) {
        $receivedRequest = $request;
        $receivedHandler = $handler;

        $response = Mockery::mock(ResponseInterface::class);
        return $response;
    };

    $notFound = new NotFound($closure);

    $result = $notFound->process($this->request, $this->handler);

    expect($receivedRequest)->toBe($this->request)
        ->and($receivedHandler)->toBe($this->handler)
        ->and($result)->toBeInstanceOf(ResponseInterface::class);
});

test('NotFound with closure can create custom response', function () {
    $customResponse = Mockery::mock(ResponseInterface::class);

    $closure = function () use ($customResponse) {
        return $customResponse;
    };

    $notFound = new NotFound($closure);

    $result = $notFound->process($this->request, $this->handler);

    expect($result)->toBe($customResponse);
});

test('NotFound is readonly', function () {
    $notFound = new NotFound($this->response);

    $reflection = new ReflectionClass(NotFound::class);
    expect($notFound)->toBeInstanceOf(NotFound::class)
        ->and($reflection->isReadOnly())->toBeTrue();
});

