<?php

use JDS\EventDispatcher\EventDispatcher;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Request;
use JDS\Http\Response;

function makeRequest(): Request
{
    return new Request(
        method: 'GET',
        uri: '/test',
        pathInfo: '/test',
    );
}

it('1. runs listeners in order and retains the same event instance', function () {
    $dispatcher = new EventDispatcher();
    $log = [];

    $dispatcher->addListener(ResponseEvent::class, function (ResponseEvent $event) use (&$log) {
        $log[] = 'first';
    });

    $dispatcher->addListener(ResponseEvent::class, function (ResponseEvent $event) use (&$log) {
        $log[] = 'second';
    });

    $event = new ResponseEvent(makeRequest(), new Response());
    $returned = $dispatcher->dispatch($event);

    expect($log)->toBe(['first', 'second']);
    expect($returned)->toBe($event);
});

it('2. allows listeners to modify or replace the response', function () {
    $dispatcher = new EventDispatcher();

    $dispatcher->addListener(ResponseEvent::class, function (ResponseEvent $event) {
        $new = new Response('modified content', 201, []);
        $event->setResponse($new);
    });

    $event = new ResponseEvent(makeRequest(), new Response('original', 200, []));
    $dispatcher->dispatch($event);

    expect($event->getResponse()->getStatusCode())->toBe(201);
    expect($event->getResponse()->getContent())->toBe('modified content');
});

it('3. stops propagation when stopPropagation() is called', function () {
    $dispatcher = new EventDispatcher();
    $log = [];

    $dispatcher->addListener(ResponseEvent::class, function (ResponseEvent $event) use (&$log) {
        $log[] = 'first';
        $event->stopPropagation();
    });

    $dispatcher->addListener(ResponseEvent::class, function () use (&$log) {
        $log[] = 'should_not_run';
    });

    $event = new ResponseEvent(makeRequest(), new Response());
    $dispatcher->dispatch($event);

    expect($log)->toBe(['first']);
});

it('4. passes silently when there are no listeners', function () {
    $dispatcher = new EventDispatcher();
    $event = new ResponseEvent(makeRequest(), new Response());

    $returned = $dispatcher->dispatch($event);

    expect($returned)->toBe($event);
});










