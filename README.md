# FastRoute Router

A PSR-15 router implementation using [nikic/fast-route](https://github.com/nikic/FastRoute) as the routing engine.

## Installation

Install via Composer:

```bash
composer require debuss-a/fastroute-router
```

## Requirements

* PHP 8.2 or higher
* nikic/fast-route ^1.0

## Usage

An example controller :

```php
<?php

namespace Application\Controller;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Router\Attribute\Method;

class HomePageController extends RequestHandlerInterface
{

    #[Method('/', methods: ['GET'])]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->logger->info('Displaying home page');

        return $this->response()->view('home');
    }
}
```

Use the router and attribute route loader in your application:

```php
$request = ServerRequestFactory::fromGlobals();

$dispatcher = simpleDispatcher(function (RouteCollector $collector): void {

    // This part will scan the specified directory for
    // controller classes and load their route attributes
    $loader = new AttributeRouteLoader(
        'Application\\Controller\\',
        source_path('Application/Controller')
    );

    $loader->load($collector);
    
    // You can also add routes manually if needed
    $collector->addRoute('GET', '/about', Application\Controller\AboutPageController::class);

});

$response = $dispatcher->dispatch($request->getMethod(), $request->getUri());
```

The handler can be :

- instanceof MiddlewareInterface
- instanceof RequestHandlerInterface
- a callable

## Testing

This project includes a comprehensive Pest test suite with **87+ passing tests** covering all components.

### Running Tests

```bash
./vendor/bin/pest
```

## License

MIT

