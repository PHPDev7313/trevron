<?php

use JDS\EventDispatcher\EventDispatcher;
use Tests\Stubs\EventDispatcher\FakeEvent;
use Tests\Stubs\EventDispatcher\FakeStoppableEvent;

beforeEach(function () {
    $this->dispatcher = new EventDispatcher();
});

it('1. dispatches event to registered listeners in order', function () {
    $event = new FakeEvent();

    $log = [];

    $this->dispatcher->addListener(FakeEvent::class, function () use (&$log){
        $log[] = 'first';
    });

    $this->dispatcher->addListener(FakeEvent::class, function () use (&$log){
        $log[] = 'second';
    });

    $this->dispatcher->dispatch($event);

    expect($log)->toBe(['first', 'second']);
});

it('2. passes the SAME event instance to all listeners', function () {
    $event = new FakeEvent();

    $this->dispatcher->addListener(FakeEvent::class, function (FakeEvent $e) {
        $e->value = 'changed';
    });

    $returned = $this->dispatcher->dispatch($event);

    expect($returned)->toBe($event)
        ->and($event->value)->toBe('changed');
});

it('3. returns the event instance when there are no listeners', function () {

    $event = new FakeEvent();

    $returned = $this->dispatcher->dispatch($event);

    expect($returned)->toBe($event);
});

it('4. listener can mutate the event', function () {

    $event = new FakeEvent();
    $event->value = 'initial';

    $this->dispatcher->addListener(FakeEvent::class, function (FakeEvent $e) {
        $e->value = 'modified';
    });

    $this->dispatcher->dispatch($event);

    expect($event->value)->toBe('modified');
});

it('5. stops propagation when stopPropagation() is called', function () {

    $event = new FakeStoppableEvent();

    $this->dispatcher->addListener(FakeStoppableEvent::class, function (FakeStoppableEvent $e) {
        $e->log[] = 'first';
        $e->stopPropagation();
    });

    $this->dispatcher->addListener(FakeStoppableEvent::class, function (FakeStoppableEvent $e) {
        $e->log[] = 'second'; // should never run
    });

    $this->dispatcher->dispatch($event);

    expect($event->log)->toBe(['first']);
});

it('6. dispatch calls no listeners for unrelated event types', function () {

    $this->dispatcher->addListener('SomeOtherEvent', function () {
        throw new Exception("This should never fire");
    });

    $event = new FakeEvent();

    //
    // No exception = success
    //
    $this->dispatcher->dispatch($event);

    expect(true)->toBeTrue();
});

it('7. dispatch does not swallow listener exceptions', function () {

    $event = new FakeEvent();

    $this->dispatcher->addListener(FakeEvent::class, function () {
        throw new RuntimeException("boom");
    });

    expect(fn() => $this->dispatcher->dispatch($event))
        ->toThrow(RuntimeException::class, "boom");
});





