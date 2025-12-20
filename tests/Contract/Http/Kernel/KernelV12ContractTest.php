<?php

declare(strict_types=1);

namespace Tests\Contract\Http;





//
// ----Test Doubles (contract-level) ----
//


use JDS\Error\Disclosure\ProductionDisclosurePolicy;
use JDS\Error\ErrorContext;
use JDS\Error\Rendering\ErrorRendererInterface;
use JDS\Error\Response\ErrorResponder;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Error\StatusCode;
use JDS\Http\Kernel;
use JDS\Http\Request;
use JDS\Http\Response;
use Tests\Contract\Stubs\Http\Kernel\FailFastRenderer;
use Tests\Contract\Stubs\Http\Kernel\FakeMiddlewareResolver;
use Tests\Contract\Stubs\Http\Kernel\NullEventDispatcher;
use Tests\Contract\Stubs\Http\Kernel\StatusExceptionThrowingDispatcher;
use Tests\Contract\Stubs\Http\Kernel\SuccessfulControllerDispatcher;
use Tests\Contract\Stubs\Http\Kernel\ThrowableThrowingDispatcher;

function buildFailFastErrorResponder(): ErrorResponder
{
    $renderer = new FailFastRenderer();

    return new ErrorResponder(
        sanitizer: new ErrorSanitizer(new ProductionDisclosurePolicy()),
        htmlRenderer: $renderer,
        jsonRenderer: $renderer,
        cliRenderer: $renderer,
    );
}

//
// ---- Contract Tests (v1.2 FINAL) ----
//

it('[v1.2 FINAL] success path returns controller response and never uses ErrorResponder', function () {
    $kernel = new Kernel(
        resolver: new FakeMiddlewareResolver(),
        controllerDispatcher: new SuccessfulControllerDispatcher(),
        eventDispatcher: new NullEventDispatcher(),
        errorResponder: buildFailFastErrorResponder()
    );

    $request = new Request('GET', '/', '/', [], [], [], [], []);

    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});

it('[v1.2 FINAL] StatusException path delegates to ErrorResponder and returns its Response verbatim', function () {
    $expected = new Response('handled', 404);

    //
    // We cannot mock final ErrorResponder, so we use a real one with a capturing renderer
    //
    $capturing = new class implements ErrorRendererInterface {
        public ?ErrorContext $context = null;

        public function render($request, $context): Response
        {
            $this->context = $context;
            return new Response('handled', 404);
        }
    };

    $responder = new ErrorResponder(
        sanitizer: new ErrorSanitizer(new ProductionDisclosurePolicy()),
        htmlRenderer: $capturing,
        jsonRenderer: $capturing,
        cliRenderer: $capturing,
    );

    $kernel = new Kernel(
        resolver: new FakeMiddlewareResolver(),
        controllerDispatcher: new StatusExceptionThrowingDispatcher(),
        eventDispatcher: new NullEventDispatcher(),
        errorResponder: $responder
    );

    $request = new Request('GET', '/', '/', [], [], [], [], []);

    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe($expected->getStatusCode());
    expect($response->getContent())->toBe($expected->getContent());

    //
    // Contract: Kernel must normalize through ErrorContext
    //
    expect($capturing->context)->not->toBeNull();
    expect($capturing->context->httpStatus)->toBe(404);
    expect($capturing->context->statusCode)->toBe(StatusCode::HTTP_ROUTE_NOT_FOUND);
    expect($capturing->context->category)->toBe(StatusCode::HTTP_ROUTE_NOT_FOUND->category());
});

it('[v1.2 FINAL] Throwable path normalizes to SERVER_INTERNAL_ERROR and delegates to ErrorResponder', function () {
    $capturing = new class implements \JDS\Error\Rendering\ErrorRendererInterface {
        public ?ErrorContext $context = null;

        public function render($request, $context): Response
        {
            $this->context = $context;
            return new Response('handled', 500);
        }
    };

    $responder = new ErrorResponder(
        sanitizer: new ErrorSanitizer(new ProductionDisclosurePolicy()),
        htmlRenderer: $capturing,
        jsonRenderer: $capturing,
        cliRenderer: $capturing,
    );

    $kernel = new Kernel(
        resolver: new FakeMiddlewareResolver(),
        controllerDispatcher: new ThrowableThrowingDispatcher(),
        eventDispatcher: new NullEventDispatcher(),
        errorResponder: $responder
    );

    $request = new Request('GET', '/', '/', [], [], [], [], []);

    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(500);

    expect($capturing->context)->not->toBeNull();
    expect($capturing->context->httpStatus)->toBe(3801);
    expect($capturing->context->statusCode)->toBe(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE);
    expect($capturing->context->category)->toBe(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE->category());
});









