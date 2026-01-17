<?php
/*
 * Trevron Framework - v1.2 FINAL
 *
 * Controller Render Contract
 */
declare(strict_types=1);

use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Contracts\Http\Rendering\HttpRendererInterface;
use JDS\Controller\AbstractController;
use JDS\Error\StatusCode;
use JDS\Http\Rendering\HttpRenderer;
use JDS\Http\Response;
use League\Container\Container;
use Tests\Contract\Stubs\Http\Controller\TestController;
use Tests\Contract\Stubs\Http\Rendering\TestHttpRenderer;
use Twig\Error\LoaderError;
use JDS\Exceptions\Http\HttpRuntimeException;
beforeEach(function () {
    $this->container = new Container();
});

it('1. [v1.2 FINAL] render returns a Response instance', function () {
    $engine = Mockery::mock(TemplateEngineInterface::class);
    $engine->shouldReceive('render')
        ->once()
        ->with('test.html.twig', ['name' => 'Trevron'])
        ->andReturn('<h1>Hello</h1>');

    $this->container->add(TemplateEngineInterface::class, $engine);

    $this->container->add(HttpRendererInterface::class, function () use ($engine) {
        return new TestHttpRenderer($engine);
    });

    $controller = new TestController();
    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response)
        ->toBeInstanceOf(Response::class);
});

it('2. [v1.2 FINAL] render sets HTML body and content-type', function () {
    $engine = Mockery::mock(TemplateEngineInterface::class);
    $engine->shouldReceive('render')
        ->once()
        ->with('test.html.twig', ['name' => 'Trevron'])
        ->andReturn('<h1>Hello Trevron</h1>');

    $this->container->add(TemplateEngineInterface::class, $engine);

    $this->container->add(HttpRendererInterface::class, function () use ($engine) {
        return new TestHttpRenderer($engine);
    });

    $controller = new TestController();
    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response->getContent())
        ->toContain('Hello Trevron');

    expect($response->getHeader('Content-Type'))
        ->toBe('text/html; charset=utf-8');
});

it('3. [v1.2 FINAL] render defaults to HTTP 200', function () {
    $engine = Mockery::mock(TemplateEngineInterface::class);
    $engine->shouldReceive('render')->andReturn('OK');

    $this->container->add(TemplateEngineInterface::class, $engine);

    $this->container->add(HttpRendererInterface::class, function () use ($engine) {
        return new TestHttpRenderer($engine);
    });

    $controller = new TestController();
    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response->getStatusCode())->toBe(200);
});

it('4. [v1.2 FINAL] render honors explicit status at render time', function () {
    $engine = Mockery::mock(TemplateEngineInterface::class);
    $engine->shouldReceive('render')
        ->once()
        ->andReturn('OK');

    $this->container->add(TemplateEngineInterface::class, $engine);

    $this->container->add(HttpRendererInterface::class, function () use ($engine) {
        return new TestHttpRenderer($engine);
    });

    $controller = new class extends AbstractController {
        public function index(): Response
        {
            return $this->render(
                'x.twig',
                [],
                StatusCode::HTTP_CREATED->value
            );
        }
    };

    $controller->setContainer($this->container);

    $response = $controller->index();

    expect($response->getStatusCode())
        ->toBe(StatusCode::HTTP_CREATED->value);
});

it('5. [v1.2 FINAL] render fails closed on template errors', function () {
    $engine = Mockery::mock(TemplateEngineInterface::class);
    $engine->shouldReceive('render')
        ->once()
        ->andThrow(new LoaderError('Template missing'));

    $this->container->add(TemplateEngineInterface::class, $engine);

    $this->container->add(HttpRendererInterface::class, function () use ($engine) {
        return new HttpRenderer($engine);
    });

    $controller = new TestController();
    $controller->setContainer($this->container);

    $controller->index();
})->throws(HttpRuntimeException::class);

