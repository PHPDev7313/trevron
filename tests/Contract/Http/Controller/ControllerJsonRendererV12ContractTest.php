<?php
declare(strict_types=1);

/*
 * Trevron Framework - v1.2 FINAL
 *
 * Controller JSON Render Contract
 */

use JDS\Contracts\Http\Rendering\JsonRendererInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Http\HttpRuntimeException;
use JDS\Http\Rendering\JsonRenderer;
use JDS\Http\Response;
use League\Container\Container;
use Tests\Contract\Stubs\Http\Controller\TestController;

beforeEach(function () {
    $this->container = new Container();
});

it('1. [1.2 FINAL] json reader returns a Response instance', function () {
    $this->container->add(JsonRendererInterface::class, new JsonRenderer());

    $controller = new class extends TestController {
        public function index(): Response
        {
            return $this->jsonRender(['ok' => true]);
        }
    };

    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response)->toBeInstanceOf(Response::class);
});

it('2. [v1.2 FINAL] json render sets application/json content-type', function () {
    $this->container->add(JsonRendererInterface::class, new JsonRenderer());

    $controller = new class extends TestController {
        public function index(): Response
        {
            return $this->jsonRender(['foo' => 'bar']);
        }
    };

    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response->getHeader('Content-Type'))
        ->toBe('application/json');
});

it('3. [v1.2 FINAL] json render honors explicit HTTP status', function () {
    $this->container->add(JsonRendererInterface::class, new JsonRenderer());

    $controller = new class extends TestController {
        public function index(): Response
        {
            return $this->jsonRender(
                ['created' => true],
                StatusCode::HTTP_CREATED->value
            );
        }
    };

    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response->getStatusCode())
        ->toBe(StatusCode::HTTP_CREATED->value);
});

it('4. [v1.2 FINAL] json render fails closed on encoding errors', function () {
    $this->container->add(JsonRendererInterface::class, new JsonRenderer());

    $controller = new class extends TestController {
        public function index(): Response
        {
            return $this->jsonRender(
                ['bad' => fopen('php://memory', 'r')]
            );
        }
    };

    $controller->setContainer($this->container);

    $controller->index();
})->throws(HttpRuntimeException::class);

