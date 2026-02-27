<?php

namespace Router;

use Router\Attribute\{Group, Method};
use FastRoute\RouteCollector;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;

class AttributeRouteLoader
{
    private readonly string $namespace;
    private readonly string $path;
    /** @var Route[] */
    private array $routes = [];

    public function __construct(string $namespace, string $path)
    {
        $this->namespace = rtrim($namespace, '\\') . '\\';
        $this->path = rtrim($path, '\\/');
    }

    /**
     * @throws ReflectionException
     */
    public function load(RouteCollector $collector): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            // Convert file path to Class Name (PSR-4 assumption)
            $relativePath = str_replace([$this->path, '.php', '/'], ['', '', '\\'], $file->getPathname());
            $className = $this->namespace . trim($relativePath, '\\');

            if (!class_exists($className)) {
                continue;
            }

            $this->registerClassRoutes($className);
        }

        // Sort routes by priority ascending, 0 is the highest priority
        usort(
            $this->routes,
            fn(Route $a, Route $b) => $a->priority <=> $b->priority
        );

        // Register routes
        array_walk(
            $this->routes,
            fn(Route $route) => $collector->addRoute($route->methods, $route->path, $route)
        );
    }

    /**
     * @param class-string $className
     * @throws ReflectionException
     */
    private function registerClassRoutes(string $className): void
    {
        $reflectionClass = new ReflectionClass($className);

        $group = '';
        $groupPriority = 0;

        $groupAttribute = $reflectionClass->getAttributes(Group::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($groupAttribute) > 0) {
            /** @var Group $groupInstance */
            $groupInstance = $groupAttribute[0]->newInstance();

            $group = trim($groupInstance->path, '/');
            $groupPriority = $groupInstance->priority ?: 0;
        }

        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes(Method::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $attribute) {
                /** @var Method $methodInstance */
                $methodInstance = $attribute->newInstance();

                $pathSegment = array_merge(
                    explode('/', $group),
                    explode('/', trim($methodInstance->path, '/'))
                );

                $methods = $methodInstance->methods;
                $path = '/' . implode(
                    '/',
                    array_filter($pathSegment, static fn(string $segment): bool => $segment !== '')
                );
                $name = $methodInstance->name ?: sprintf(
                    '%s^%s',
                    implode(':', $methods),
                    $this->path
                );
                $priority = $groupPriority + ($methodInstance->priority ?: 0);

                $this->routes[] = new Route(
                    $methods,
                    $path,
                    $className,
                    $name,
                    $priority
                );
            }
        }
    }
}
