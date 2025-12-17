<?php

use JDS\Error\ErrorContext;
use JDS\Error\Rendering\JsonErrorRenderer;
use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;
use JDS\Http\Request;

it('renders a production safe json error payload', function () {
    $renderer = new JsonErrorRenderer();

    $request = Mockery::mock(Request::class);

    $context = new ErrorContext(
        httpStatus: 404,
        statusCode: StatusCode::HTTP_ROUTE_NOT_FOUND,
        category: StatusCategory::Http,
        publicMessage: StatusCode::HTTP_ROUTE_NOT_FOUND->defaultMessage(),
        exception: null,
        debug: []
    );

    $response = $renderer->render($request, $context);

    expect($response->getStatusCode())->toBe(404);

    $payload = json_decode($response->getContent(), true);

    expect($payload)->toHaveKey('error')
        ->and($payload['error'])->toMatchArray([
            'status' => 404,
            'code' => [
                'key' => 'HTTP_ROUTE_NOT_FOUND',
                'value' => 404,
            ],
            'category' => [
                'key' => 'Http',
                'value' => 3800,
            ],
            'message' => StatusCode::HTTP_ROUTE_NOT_FOUND->defaultMessage(),
        ])
        ->and($payload['error'])->not->toHaveKey('debug');
});

it('includes debug data when present in context', function () {
    $renderer = new JsonErrorRenderer();

    $request = Mockery::mock(Request::class);

    $context = new ErrorContext(
        httpStatus: 500,
        statusCode: StatusCode::SERVER_INTERNAL_ERROR,
        category: StatusCategory::Server,
        publicMessage: 'Internal Server Error',
        exception: null,
        debug: [
            'exception_class' => 'RuntimeException',
            'message' => 'boom'
        ]
    );

    $response = $renderer->render($request, $context);

    $payload = json_decode($response->getContent(), true);

    expect($payload['error'])->toHaveKey('debug')
        ->and($payload['error']['debug'])->toBe([
            'exception_class' => 'RuntimeException',
            'message' => 'boom'
        ]);
});




