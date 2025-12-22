<?php

use JDS\Error\Disclosure\ProductionDisclosurePolicy;
use JDS\Error\ErrorContext;
use JDS\Error\Response\ErrorResponder;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;
use JDS\Http\Request;
use Tests\Unit\Error\Response\FakeRenderer;

it('uses json renderer when request expects json', function () {
    $html = new FakeRenderer();
    $json = new FakeRenderer();
    $cli  = new FakeRenderer();

    $responder = new ErrorResponder(
        new ErrorSanitizer(new ProductionDisclosurePolicy()),
        $html,
        $json,
        $cli
    );

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('isCli')
        ->once()
        ->andReturn(false);

    $request->shouldReceive('expectsJson')
        ->once()
        ->andReturn(true);

    $context = new ErrorContext(
        500,
        StatusCode::SERVER_INTERNAL_ERROR,
        StatusCategory::Server,
        'Error'
    );

    $responder->respond($request, $context);

    expect($json->called)->toBeTrue();
    expect($html->called)->toBeFalse();
});

it('uses html renderer by default', function () {
    $html = new FakeRenderer();
    $json = new FakeRenderer();
    $cli  = new FakeRenderer();

    $responder = new ErrorResponder(
        new ErrorSanitizer(new ProductionDisclosurePolicy()),
        $html,
        $json,
        $cli
    );

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('isCli')->once()->andReturn(false);
    $request->shouldReceive('expectsJson')->once()->andReturn(false);

    $context = new ErrorContext(
        500,
        StatusCode::SERVER_INTERNAL_ERROR,
        StatusCategory::Server,
        'Error'
    );

    $responder->respond($request, $context);

    expect($html->called)->toBeTrue();
    expect($json->called)->toBeFalse();
    expect($cli->called)->toBeFalse();
});

it('uses cli renderer when request is cli even if json is expected', function () {
    $html = new FakeRenderer();
    $json = new FakeRenderer();
    $cli  = new FakeRenderer();

    $responder = new ErrorResponder(
        new ErrorSanitizer(new ProductionDisclosurePolicy()),
        $html,
        $json,
        $cli
    );

    $request = Mockery::mock(Request::class);
    $request->shouldReceive('isCli')->once()->andReturn(true);
    $request->shouldReceive('expectsJson')->never();

    $context = new ErrorContext(
        500,
        StatusCode::SERVER_INTERNAL_ERROR,
        StatusCategory::Server,
        'Error'
    );

    $responder->respond($request, $context);

    expect($cli->called)->toBeTrue();
    expect($html->called)->toBeFalse();
    expect($json->called)->toBeFalse();
});

