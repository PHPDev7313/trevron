<?php

use JDS\Error\Disclosure\ProductionDisclosurePolicy;
use JDS\Error\Response\ErrorResponder;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Http\Kernel;
use JDS\Http\Request;
use Tests\Stubs\Http\Kernel\FailFastRenderer;
use Tests\Stubs\Http\Kernel\FakeMiddlewareResolver;
use Tests\Stubs\Http\Kernel\NullEventDispatcher;
use Tests\Stubs\Http\Kernel\SuccessfulControllerDispatcher;

it('returns controller response on successful request', function () {
    $kernel = new Kernel(
        resolver: new FakeMiddlewareResolver(),
        controllerDispatcher: new SuccessfulControllerDispatcher(),
        eventDispatcher: new NullEventDispatcher(),
        errorResponder: buildFailFastErrorResponder()
    );

    $request = new Request(
        'GET',
        '/',
        '/',
        [],
        [],
        [],
        [],
        []
    );

    $response = $kernel->handle($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('OK');
});



function buildFailFastErrorResponder(): ErrorResponder
{
    $renderer = new FailFastRenderer();

    return new ErrorResponder(
        sanitizer: new ErrorSanitizer(
            new ProductionDisclosurePolicy()
            ),
        htmlRenderer: $renderer,
        jsonRenderer: $renderer,
        cliRenderer: $renderer,
    );
}



