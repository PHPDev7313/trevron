<?php

use JDS\Contracts\Rendering\RendererInterface;
use JDS\Exceptions\Controller\ControllerInvocationException;
use JDS\Exceptions\Controller\ControllerMethodNotFoundException;
use JDS\Exceptions\Controller\ControllerNotFoundException;
use JDS\Http\ControllerDispatcher;
use JDS\Http\Response;
use JDS\Routing\Route;
use Psr\Container\ContainerInterface;
use Tests\Stubs\Controller\TestController;
use Tests\Stubs\Http\FakeRequest;
use Tests\Stubs\Template\FakeRenderer;

beforeEach(function () {
    $this->renderer = new FakeRenderer();


    //
    // Fake container
    //
    $this->container = new class($this->renderer) implements ContainerInterface {
        private array $services;

        public function __construct($renderer)
        {
            $this->services = [
                TestController::class => new TestController(),
                RendererInterface::class => $renderer,
            ];
        }

        public function get(string $id)
        {
            if (!$this->has($id)) {
                throw new RuntimeException("Service $id not found");
            }
            return $this->services[$id] ?? null;
        }

        public function has(string $id): bool
        {
            return isset($this->services[$id]);
        }
    };

    $this->dispatcher = new ControllerDispatcher($this->container);

});

/**
 * Utility to quickly construct a request + route
 */
function makeReq(string $method, string $path, string $controllerMethod, array $params = [])
{
    //
    // FakeRequest(method, uri, pathInfo)
    $req = new FakeRequest($method, $path, $path);

    //
    // Create route: method + path + handler + middleware
    //
    $route = new Route($method, $path, [TestController::class, $controllerMethod], []);

    //
    // Attach route + route params to the Request
    //
    $req->setRoute($route);
    $req->setRouteParams($params);

    return $req;
}

it('1. Dispathes a simple controller returning a Response', function () {
    $req = makeReq("GET", "/", "simple");

    $res = $this->dispatcher->dispatch($req);

    expect($res)->toBeInstanceOf(Response::class)
        ->and ($res->getContent())->toBe("ok");
});

it('2. Injects route params correctly', function () {
    $req = makeReq("GET", "/user/55", "withParams", ["id" => "55"]);

    $res = $this->dispatcher->dispatch($req);

    expect($res->getContent())->toBe("id:55");
});

it('3. Injects Request automatically when type-hinted', function () {
    $req = makeReq("POST", "/x", "withRequest");

    $res = $this->dispatcher->dispatch($req);

    expect($res->getContent())->toBe("method:POST");
});

it('4. Converts TemplateResponse using RendererInterface', function () {
    $req = makeReq("GET", "/", "template");

    $res = $this->dispatcher->dispatch($req);

    expect($res)->toBeInstanceOf(Response::class)
        ->and($res->getContent())->toBe("rendered:hello.twig")
        ->and($this->renderer->last)->toMatchArray([
            'template' => "hello.twig",
            "params" => ["x" => 1],
            ]);
});

it('5. Throws when controller is not in container', function () {
    $req = new FakeRequest("GET", '/missing', '/missing');

    //
    // Fake route pointing to non-existent controller
    $route = new Route("GET", "/missing", ["MissingClass", "simple"], []);
    $req->setRoute($route);
    $req->setRouteParams([]);

    $this->dispatcher->dispatch($req);
})->throws(ControllerNotFoundException::class);

it('6. Throws when controller method does not exist', function () {
    $req = makeReq("GET", "/", "noSuchMethod");

    $this->dispatcher->dispatch($req);

})->throws(ControllerMethodNotFoundException::class);

it('7. Throws when controller returns invalid value', function () {
    $req = makeReq("GET", "/", "invalid");

    $this->dispatcher->dispatch($req);
})->throws(ControllerInvocationException::class);

it('8. Throws when missing required route arguments', function () {
    //
    // withParams(string $id) but no 'id' provided
    $req = makeReq("GET", "/", "withParams", []);

    $this->dispatcher->dispatch($req);

})->throws(ControllerInvocationException::class);

it('9. Throws when no Route is attached to Request', function () {
    $req = new FakeRequest("GET", "/", "/");

    $this->dispatcher->dispatch($req);

})->throws(ControllerInvocationException::class);

it('10. Injects a 12-character string userId param', function () {

    // Example real-world primary key from your system
    $id = "fK4gt3Kwq90k";

    $req = makeReq(
        "GET",
        "/user/{$id}",
        "withParams",
        ["id" => $id]
    );

    $res = $this->dispatcher->dispatch($req);

    expect($res->getContent())->toBe("id:{$id}");
});

it('11. Injects userId param correctly', function () {
    $id = "fK4gt3Kwq90k";
    $req = makeReq(
        "GET",
        '/user/{$id}',
        'withUserId',
        ["userId" => $id]
    );

    $res = $this->dispatcher->dispatch($req);

    expect($res->getContent())->toBe("userId:{$id}");

});







