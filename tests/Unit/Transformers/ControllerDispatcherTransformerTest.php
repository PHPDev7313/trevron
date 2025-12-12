<?php

use JDS\Exceptions\Validation\ValidationException;
use JDS\Http\ControllerDispatcher;
use JDS\Http\Response;
use JDS\Routing\Route;
use JDS\Transformers\TransformerManager;
use JDS\Validation\MethodParameterValidator;
use Psr\Container\ContainerInterface;
use Tests\Stubs\Http\FakeRequest;
use Tests\Stubs\Transformers\FakeUser;
use Tests\Stubs\Transformers\FakeUserController;
use Tests\Stubs\Transformers\FakeUserRepository;
use Tests\Stubs\Transformers\FakeUserTransformer;

beforeEach(function () {
    //
    // Seed repo with one user
    //
    $repo = new FakeUserRepository([
        'fK4gt3Kwq90k' => new FakeUser('fK4gt3Kwq90k', 'Test User'),
    ]);

    $transformerManager = new TransformerManager();
    $transformerManager->addTransformer(new FakeUserTransformer($repo));

    $validator = new MethodParameterValidator();

    //
    // Minimal fake container for this integration test
    //
    $this->container = new class($transformerManager, $validator) implements \Psr\Container\ContainerInterface {
        private array $services = [];

        public function __construct(TransformerManager $tm, MethodParameterValidator $validator)
        {
            $this->services = [
                \Tests\Stubs\Transformers\FakeUserController::class => new FakeUserController(),
                TransformerManager::class => $tm,
                MethodParameterValidator::class => $validator,
            ];
        }

        public function get(string $id)
        {
            if (!$this->has($id)) {
                throw new RuntimeException("Service {$id} not found.");
            }
            return $this->services[$id];
        }

        public function has(string $id): bool
        {
            return isset($this->services[$id]);
        }
    };
    $this->dispatcher = new ControllerDispatcher(
        $this->container,
        new MethodParameterValidator(),
        $transformerManager
    );
});

it('1. hydrates FakeUser via transformer and injects into controller', function () {
    $id = "fK4gt3Kwq90k";

    //
    // FakeRequest(method, uri, pathInfo)
    //
    $req = new FakeRequest("GET", "/user/{$id}", "/user/{$id}");

    //
    // Route(method, path, [ControllerClass, method], middleware[])
    //
    $route = new Route("GET", "/user/{$id}", [FakeUserController::class, 'show'], []);

    //
    // Route params: controller param name = "userId"
    //
    $req->setRoute($route);
    $req->setRouteParams([
        "userId" => $id,
    ]);

    $res = $this->dispatcher->dispatch($req);

    expect($res)->toBeInstanceOf(Response::class)
        ->and($res->getContent())->toBe("user:{$id}:Test User");
});

it('2. throws ValidationException when user cannot be transformed', function () {
    $id = "DoesNotExist123";

    $req = new FakeRequest("GET", "/user/{$id}", "/user/{$id}");
    $route = new Route("GET", "/user/{$id}", [FakeUserController::class, 'show'], []);

    $req->setRoute($route);
    $req->setRouteParams([
        'userId' => $id,
    ]);

    $this->dispatcher->dispatch($req);

})->throws(ValidationException::class);


it('hydrates FakeUser from an integer route param', function () {

    // repo stores int id
    $repo = new FakeUserRepository([
        42 => new FakeUser(42, "User 42"),
    ]);

    $tm = new TransformerManager();
    $tm->addTransformer(new FakeUserTransformer($repo));

    $validator = new MethodParameterValidator();

    $container = new class($tm, $validator) implements ContainerInterface {
        private array $services = [];
        public function __construct(
            private $tm,
            private $validator
        ) {
            $this->services = [
                FakeUserController::class => new FakeUserController(),
                TransformerManager::class => $tm,
                MethodParameterValidator::class => $validator,
            ];
        }

        public function get(string $id) {
            return $this->services[$id];
        }
        public function has(string $id): bool {
            return isset($this->services[$id]);
        }
    };

    $dispatcher = new ControllerDispatcher($container, $validator, $tm);

    $req = new FakeRequest('GET', '/user/42', '/user/42');
    $req->setRoute(new Route('GET', '/user/42', [FakeUserController::class, 'show']));
    $req->setRouteParams(['userId' => '42']); // incoming as string from router

    $res = $dispatcher->dispatch($req);

    expect($res->getContent())->toBe("user:42:User 42");
});







