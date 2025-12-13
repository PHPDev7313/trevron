<?php


use JDS\Error\StatusCategory;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

it('1. uses the default message from the status code when none is provided', function () {
    $exception = new StatusException(StatusCode::ENTITY_NOT_FOUND);

    expect($exception->getMessage())
        ->toBe(StatusCode::ENTITY_NOT_FOUND->defaultMessage());

    expect($exception->getCode())
        ->toBe(StatusCode::ENTITY_NOT_FOUND->value);
});

it('2. uses a custom message when one is provided', function () {
    $exception = new StatusException(
        StatusCode::ENTITY_NOT_FOUND,
        'Custom entity not found message'
    );

    expect($exception->getMessage())
        ->toBe('Custom entity not found message');
});

it('3. exposes the underlying StatusCode enum', function () {
    $exception = new StatusException(StatusCode::FORM_VALIDATION_FAILED);

    expect($exception->getStatusCodeEnum())
        ->toBe(StatusCode::FORM_VALIDATION_FAILED);
});


it('4. derives the correct status category from the status code', function () {
    $exception = new StatusException(StatusCode::DATABASE_GENERAL_ERROR);

    expect($exception->getStatusCategory())
        ->toBe(StatusCategory::Database);
});

it('5. preserves the previous exception', function () {
    $previous = new RuntimeException('Original failure');

    $exception = new StatusException(
        StatusCode::SERVER_INTERNAL_ERROR,
        null,
        $previous
    );

    expect($exception->getPrevious())
        ->toBe($previous);
});




















