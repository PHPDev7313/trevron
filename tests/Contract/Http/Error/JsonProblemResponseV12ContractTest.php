<?php
declare(strict_types=1);

/*
 * Trevron Framework - v1.2 FINAL
 *
 * JSON Error Response Contract
 */

use JDS\Contracts\Http\Rendering\JsonErrorRendererInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Rendering\JsonErrorRenderer;
use JDS\Http\Response;
use League\Container\Container;

beforeEach(function () {
    $this->container = new Container();
    $this->container->add(
        JsonErrorRendererInterface::class, new JsonErrorRenderer(debug: false)
    );
});

it('1. [v1.2 FINAL] renders StatusException as JSON problem response', function () {
    $renderer = $this->container->get(JsonErrorRendererInterface::class);

    $exception = new StatusException(
        StatusCode::TEMPLATE_RENDERING_FAILED
    );

    $response = $renderer->render($exception);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
            ->toBe(StatusCode::TEMPLATE_RENDERING_FAILED->valueInt())
        ->and($response->getHeader('Content-Type'))
            ->toBe('application/problem+json')
        ->and($response->getContent())
            ->toContain('"key":"TEMPLATE_RENDERING_FAILED"');
});

it('2. [v1.2 FINAL] unknown exception becomes HTTP 500 JSON problem', function () {
    $renderer = $this->container->get(JsonErrorRendererInterface::class);

    $response = $renderer->render(
        new RuntimeException('Boom')
    );

    expect($response->getStatusCode())
        ->toBe(500)
        ->and($response->getContent())
            ->toContain('"code":' . StatusCode::HTTP_KERNEL_GENERAL_FAILURE->valueInt());
});

it('3. [v1.2 FINAL] JSON error rendering never throws', function () {
    $renderer = new JsonErrorRenderer(debug: false);

    $response = $renderer->render(
        new RuntimeException("\xB1\x31") // invalid UTF-8
    );

    expect($response)->toBeInstanceOf(Response::class);
});

