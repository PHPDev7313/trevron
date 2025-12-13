<?php

use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Event\TerminateEvent;
use JDS\Http\Request;
use JDS\Http\Response;

function makeRequest2(): Request {
    return new Request(
        method:'GET',
        uri: '/test',
        pathInfo: '/test'
    );
}

it('1. runs terminate listeners is order and returns the same event instance', function () {
    $dispatcher = new EventDispatcher();

    $log = [];

    $dispatcher->addListener(TerminateEvent::class, function (TerminateEvent $event) use (&$log) {
        $log[] = 'first';
    });

    $dispatcher->addListener(TerminateEvent::class, function (TerminateEvent $event) use (&$log) {
        $log[] = 'second';
    });

    $event = new TerminateEvent(
        makeRequest2(),
        new Response(),
        startTime: 1.234,
        endTime: 2.345,
        duration: 1.111
    );

    $returned = $dispatcher->dispatch($event);

    expect($log)->toBe(['first', 'second']);
    expect($returned)->toBe($event);
});

it('2. exposes correct request, response, and timing values to listeners', function () {
    $dispatcher = new EventDispatcher();

    $req = makeRequest2();
    $res = new Response();

    $start = 100.0;
    $end = 150.0;
    $duration = 50.0;

    $dispatcher->addListener(TerminateEvent::class, function (TerminateEvent $event) use ($req, $res, $start, $end, $duration) {
        expect($event->getRequest())->toBe($req);
        expect($event->getResponse())->toBe($res);
        expect($event->getStartTime())->toBe($start);
        expect($event->getEndTime())->toBe($end);
        expect($event->getDuration())->toBe($duration);
    });

    $dispatcher->dispatch(
        new TerminateEvent($req, $res, $start, $end, $duration)
    );
});

it('3. stops propagation when stopPropagation() is called', function () {
    $dispatcher = new EventDispatcher();
    $log = [];

    $dispatcher->addListener(TerminateEvent::class, function (TerminateEvent $event) use (&$log) {
        $log[] = 'first';
        $event->stopPropagation();
    });

    $dispatcher->addListener(TerminateEvent::class, function () use (&$log) {
        $log[] = 'should_not_run';
    });

    $event = new TerminateEvent(
        makeRequest2(),
        new Response(),
        startTime: 1.0,
        endTime: 2.0,
        duration: 1.0
    );

    $dispatcher->dispatch($event);

    expect($log)->toBe(['first']);
});

it('4. silently passes when no listeners are registered', function () {
    $dispatcher = new EventDispatcher();

    $event = new TerminateEvent(
        makeRequest2(),
        new Response(),
        startTime: 1.0,
        endTime: 2.0,
        duration: 1.0
    );

    $returned = $dispatcher->dispatch($event);

    expect($returned)->toBe($event);
});









