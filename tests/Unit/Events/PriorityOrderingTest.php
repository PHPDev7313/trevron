<?php


use JDS\EventDispatcher\EventDispatcher;
use Tests\Stubs\Events\HighPrioritySubscriber;
use Tests\Stubs\Events\LateSubscriber;
use Tests\Stubs\Events\LowPrioritySubscriber;
use Tests\Stubs\Events\StoppingSubscriber;
use Tests\Stubs\Events\TestEvent;

it('1. executes subscribers in priority order (high to low)', function () {
    $dispatcher = new EventDispatcher();

    $dispatcher->addSubscriber(new LowPrioritySubscriber());
    $dispatcher->addSubscriber(new HighPrioritySubscriber());

    $event = new TestEvent();

    $dispatcher->dispatch($event);
    expect($event->calls)->toBe([
        'high',
        'low'
    ]);
});

it('2. stops event propagation when stopPropagation is called', function () {
    $dispatcher = new EventDispatcher();

    $dispatcher->addSubscriber(new LateSubscriber());
    $dispatcher->addSubscriber(new StoppingSubscriber());

    $event = new TestEvent();

    $dispatcher->dispatch($event);

    expect($event->calls)->toBe([
        'stopper'
    ]);
});

it('3. honors priority before stoppable propagation', function () {
    $dispatcher = new EventDispatcher();

    $dispatcher->addSubscriber(new LowPrioritySubscriber()); // -100
    $dispatcher->addSubscriber(new StoppingSubscriber()); // 100
    $dispatcher->addSubscriber(new HighPrioritySubscriber()); // 100

    $event = new TestEvent();

    $dispatcher->dispatch($event);


    expect($event->calls)->toBe([
        'stopper',
    ]);
});











