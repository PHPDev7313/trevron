<?php

use JDS\Http\ControllerDispatcher;
use JDS\Routing\Route;
use JDS\Transformers\TransformerManager;
use JDS\Validation\MethodParameterValidator;
use Psr\Container\ContainerInterface;
use Tests\Stubs\Http\FakeRequest;
use Tests\Stubs\Transformers\FakeUser;
use Tests\Stubs\Transformers\FakeUserController;
use Tests\Stubs\Transformers\FakeUserRepository;
use Tests\Stubs\Transformers\FakeUserTransformer;

beforeEach(function() {
    $this->repo = new FakeUserRepository([
        'fk4gt3Kwq90k' => new FakeUser('fk4gt3Kwq90k', 'Test User'),
    ]);

    $this->transformer = new FakeUserTransformer($this->repo);
    $this->manager = new TransformerManager();
    $this->manager->addTransformer($this->transformer);
});

it('1. reports support for registered type', function() {
    expect($this->manager->supports(FakeUser::class))->toBeTrue()
        ->and($this->manager->supports(DateTimeImmutable::class))->toBeFalse();
});

it('2. transforms value into FakeUser when supported', function() {
    $result = $this->manager->transform('fk4gt3Kwq90k', FakeUser::class);

    expect($result)->toBeInstanceOf(FakeUser::class)
        ->and($result->id)->toBe('fk4gt3Kwq90k')
        ->and($result->name)->toBe('Test User');
});

it('3. throws when transforming unsupported type', function() {
    $this->manager->transform('x', DateTimeImmutable::class);
})->throws(RuntimeException::class, "No transformer registered for type 'DateTimeImmutable'.");







