<?php

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Router\AttributeRouteLoader;

covers(AttributeRouteLoader::class);

function makeTempDir(): string
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'attr_routes_' . uniqid();
    mkdir($dir, 0777, true);
    return $dir;
}

function removeTempDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }

    rmdir($dir);
}

function writeClassFile(string $dir, string $className, string $code): string
{
    $path = $dir . DIRECTORY_SEPARATOR . $className . '.php';
    file_put_contents($path, $code);
    return $path;
}

beforeEach(function () {
    $this->tmpDir = makeTempDir();
});

afterEach(function () {
    removeTempDir($this->tmpDir);
});

it('registers attribute routes with group path', function () {
    $code = <<<'PHP'
<?php

namespace Tests\Fixtures;

use Router\Attribute\Group;
use Router\Attribute\Get;
use Router\Attribute\Post;

#[Group(path: 'api', priority:5)]
class UserController{
 #[Get(path: 'users', name: 'users.index', priority:1)]
 public function index(): void {}

 #[Post(path: 'users', name: 'users.store', priority:2)]
 public function store(): void {}
}
PHP;

    $file = writeClassFile($this->tmpDir, 'UserController', $code);
    require_once $file;

    $collector = new RouteCollector(new Std(), new GroupCountBased());
    $loader = new AttributeRouteLoader('Tests\\Fixtures', $this->tmpDir);
    $loader->load($collector);

    $data = $collector->getData();

    expect($data[0]['GET'])->toHaveKey('/api/users');
    expect($data[0]['POST'])->toHaveKey('/api/users');
});

it('does not register routes when no route attributes exist', function () {
    $code = <<<'PHP'
<?php

namespace Tests\Fixtures;

class EmptyController{
 public function index(): void {}
}
PHP;

    $file = writeClassFile($this->tmpDir, 'EmptyController', $code);
    require_once $file;

    $collector = new RouteCollector(new Std(), new GroupCountBased());
    $loader = new AttributeRouteLoader('Tests\\Fixtures', $this->tmpDir);

    $loader->load($collector);

    $data = $collector->getData();

    expect($data[0])->toBeEmpty();
    expect($data[1])->toBeEmpty();
});
