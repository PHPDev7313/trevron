<?php


use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\MiddlewareResolverInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Error\StatusCode;
use JDS\EventDispatcher\EventDispatcher;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\ControllerDispatcher;
use JDS\Http\Event\ResponseEvent;
use JDS\Http\Event\TerminateEvent;
use JDS\Http\Kernel;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\Session;
use Mockery as m;
function makeRequest(): Request
{
    return new Request(
        method: 'GET',
           uri: '/pipeline',
        pathInfo: '/pipeline',
    );
}

it('1. runs the middleware pipeline, dispatches ResponseEvent, and returns the pipeline', function () {
    $resolver =             m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher =      m::mock(EventDispatcher::class);

    $request = makeRequest();
    $response = new Response("OK", 200);

    //
    // No middleware -> Kernel will build MiddlewareQueue with an empty list
    //
    $resolver
        ->shouldReceive('getMiddlewareForRequest')
        ->once()
        ->with($request)
        ->andReturn([]);

    //
    // RouteDispatcher will use ControllerDispatcher internally.
    // We assume it calls ->dispatch($request) and retrns a Response.
    $controllerDispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->with($request)
        ->andReturn($response);

    //
    // Capture the ResponseEvent the kernel dispatches
    //
    $capturedResponseEvent = null;

    $eventDispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function ($event) use (&$capturedResponseEvent, $request, $response) {
            $capturedResponseEvent = $event;
            return $event instanceof ResponseEvent
                && $event->getRequest() === $request
                && $event->getResponse() === $response;
        }))
        ->andReturnUsing(fn ($event) => $event); // PSR-14 returns the event

    $kernel = new Kernel(
        debug: false,
        resolver: $resolver,
        controllerDispatcher: $controllerDispatcher,
        eventDispatcher: $eventDispatcher,
    );

    $result = $kernel->handle($request);

    //
    // Kernel returns the same Response the pipeline produced
    //
    expect($result)->toBe($response);

    //
    // Request start time was set
    //
    expect($request->getStartTime())->toBeGreaterThan(0.0);

    //
    // ResponseEvent was dispatched with correct data
    //
    expect($capturedResponseEvent)->toBeInstanceOf(ResponseEvent::class);
    expect($capturedResponseEvent->getRequest())->toBe($request);
    expect($capturedResponseEvent->getResponse())->toBe($response);
});

it('2. dispatches TerminateEvent with timing info and clears session flash', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $response = new Response('OK', 200);

    // Simulate a known start time (as if handle() ran before)
    $startTime = 100.0;
    $request->setStartTime($startTime);

    // Attach a session and ensure clearFlash() is called
    $session = m::mock(Session::class);
    $session->shouldReceive('clearFlash')->once();
    $request->setSession($session);

    $capturedTerminateEvent = null;

    $eventDispatcher
        ->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function ($event) use (&$capturedTerminateEvent, $request, $response, $startTime) {
            if (! $event instanceof TerminateEvent) {
                return false;
            }

            $capturedTerminateEvent = $event;

            // Basic structural checks here; deeper asserts below
            return $event->getRequest() === $request
                && $event->getResponse() === $response
                && $event->getStartTime() === $startTime;
        }))
        ->andReturnUsing(fn ($event) => $event);

    $kernel = new Kernel(
        debug: false,
        resolver: $resolver,
        controllerDispatcher: $controllerDispatcher,
        eventDispatcher: $eventDispatcher
    );

    $kernel->terminate($request, $response);

    // TerminateEvent was dispatched and captured
    expect($capturedTerminateEvent)->toBeInstanceOf(TerminateEvent::class);
    expect($capturedTerminateEvent->getRequest())->toBe($request);
    expect($capturedTerminateEvent->getResponse())->toBe($response);

    // Timing invariants:
    $endTime  = $capturedTerminateEvent->getEndTime();
    $duration = $capturedTerminateEvent->getDuration();

    expect($capturedTerminateEvent->getStartTime())->toBe($startTime);
    expect($endTime)->toBeGreaterThanOrEqual($startTime);
    expect($duration)->toBe($endTime - $startTime)
        ->and($duration)->toBeGreaterThan(0.0);
});

it('3. executes middleware in sequence then calls RouteDispatcher, fires ResponseEvent, and returns the final response', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $response = new Response("FINAL", 200);

    $log = [];

    //
    // Create two middleware to test the call chain
    //
    $mw1 = m::mock(MiddlewareInterface::class);
    $mw2 = m::mock(MiddlewareInterface::class);

    //
    // Expect middleware list to return both middleware in correct order
    //
    $resolver->shouldReceive('getMiddlewareForRequest')
        ->once()
        ->with($request)
        ->andReturn([$mw1, $mw2]);

    //
    // mw1: logs entry and calls ->handle on next handler
    //
    $mw1->shouldReceive('process')
        ->once()
        ->with($request, m::type(RequestHandlerInterface::class))
        ->andReturnUsing(function ($req, RequestHandlerInterface $next) use (&$log) {
            $log[] = "mw1_enter";
            $resp = $next->handle($req);
            $log[] = "mw1_exit";
            return $resp;
        });

    //
    // mw2: same as mw1
    //
    $mw2->shouldReceive('process')
        ->once()
        ->with($request, m::type(RequestHandlerInterface::class))
        ->andReturnUsing(function ($req, RequestHandlerInterface $next) use (&$log) {
            $log[] = "mw2_enter";
            $resp = $next->handle($req);
            $log[] = "mw2_exit";
            return $resp;
        });

    //
    // RouteDispatcher is invoked AFTER all middleware complete
    //
    $controllerDispatcher->shouldReceive('dispatch')
        ->once()
        ->with($request)
        ->andReturn($response);

    //
    // Capture ResponseEvent
    //
    $capturedEvent = null;

    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function ($event) use (&$capturedEvent, $request, $response) {
            $capturedEvent = $event;
            return $event instanceof ResponseEvent
                && $event->getRequest() === $request
                && $event->getResponse() === $response;
        }))
        ->andReturnUsing(fn ($event) => $event);

    //
    // Create Kernel and handle request
    //
    $kernel = new Kernel(
        debug: false,
        resolver: $resolver,
        controllerDispatcher: $controllerDispatcher,
        eventDispatcher: $eventDispatcher
    );

    $result = $kernel->handle($request);

    //
    // Response should be exactly the controller-produced response
    //
    expect($result)->toBe($response);

    //
    // Middleware must have executed correctly in order
    //
    expect($log)->toBe([
        "mw1_enter",
        "mw2_enter",
        "mw2_exit",
        "mw1_exit",
    ]);

    //
    // ResponseEvent was dispatched
    //
    expect($capturedEvent)->toBeInstanceOf(ResponseEvent::class);
});

it('4. allows middleware to short-circuit the pipeline and skip the controller', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request   = makeRequest();
    $shortResp = new Response("SHORT", 200);

    $mw1 = m::mock(MiddlewareInterface::class);

    $resolver->shouldReceive('getMiddlewareForRequest')
        ->once()
        ->with($request)
        ->andReturn([$mw1]);

    //
    // mw1 returns a response immediately -> does NOT call $next->handle()
    //
    $mw1->shouldReceive('process')
        ->once()
        ->andReturn($shortResp);

    //
    // Controller MUST NOT run
    //
    $controllerDispatcher->shouldNotReceive('dispatch');

    //
    // ResponseEvent still fires
    //
    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->with(m::type(ResponseEvent::class))
        ->andReturnUsing(fn ($e) => $e);

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $result = $kernel->handle($request);

    expect($result)->toBe($shortResp);
});

it('5. returns the modified response when a ResponseEvent listener replaces it', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $original = new Response("ORIGINAL", 200);
    $modified = new Response("MODIFIED", 200);

    //
    // no middleware
    //
    $resolver->shouldReceive('getMiddlewareForRequest')
        ->once()
        ->andReturn([]);

    //
    // controller returns the original response
    //
    $controllerDispatcher->shouldReceive('dispatch')
        ->once()
        ->with($request)
        ->andReturn($original);

    //
    // event listener modifies response
    //
    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function ($event) use ($modified) {
            $event->setResponse($modified);
            return true;
        }))
        ->andReturnUsing(fn ($e) => $e);

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $result = $kernel->handle($request);

    //
    // Kernel MUST honor modified response
    //
    expect($result)->toBe($modified);
});

it('6. converts StatusException into a safe Response in production mode', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request = makeRequest();

    $resolver->shouldReceive('getMiddlewareForRequest')
        ->andReturn([]);

    //
    // Controller throws a StatusException
    //
    $controllerDispatcher->shouldReceive('dispatch')
        ->andThrow(new StatusException(
            StatusCode::SERVER_INTERNAL_ERROR,
            'Controlled failure'
        ));

    //
    // ResponseEvent MUST NOT fire for exception path
    //
    $eventDispatcher->shouldNotReceive('dispatch');

    $kernel = new Kernel(
        debug: false, // production mode!
        resolver: $resolver,
        controllerDispatcher: $controllerDispatcher,
        eventDispatcher: $eventDispatcher
    );

    $result = $kernel->handle($request);

    expect($result)->toBeInstanceOf(Response::class);
    expect($result->getContent())->toBe('Controlled failure');
    expect($result->getStatusCode())->toBe(500);
});

it('7. wraps unexpected Throwables into a StatusException response in production mode', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request = makeRequest();

    $resolver->shouldReceive('getMiddlewareForRequest')
        ->andReturn([]);

    //
    // Unexpected fatal Throwable
    //
    $controllerDispatcher->shouldReceive('dispatch')
        ->andThrow(new RuntimeException("BAD THING"));

    $eventDispatcher->shouldNotReceive('dispatch');

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $result = $kernel->handle($request);
//dd($result->getStatusCode(), $result->getStatus(), StatusCode::HTTP_ROUTE_DISPATCH_FAILURE);
    expect($result)->toBeInstanceOf(Response::class)
        ->and($result->getStatusCode())->toBe(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE->value)
        ->and($result->getContent())->toContain("Route dispatch failed: BAD THING. [Route:Dispatcher].");
});

it('8. fires TerminateEvent even when no session exists', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $response = new Response("OK", 200);

    $request->setStartTime(123.0);

    //
    // Capture TerminateEvent
    //
    $captured = null;

    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function ($event) use (&$captured, $request, $response) {
            $captured = $event;
            return $event instanceof TerminateEvent
                && $event->getRequest() === $request
                && $event->getResponse() === $response;
        }))
        ->andReturnUsing(fn ($e) => $e);

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $kernel->terminate($request, $response);

    expect($captured)->toBeInstanceOf(TerminateEvent::class);
});

it('9. always sets request start time before pipeline execution even if exceptions occur', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request = makeRequest();

    $resolver->shouldReceive('getMiddlewareForRequest')
        ->once()
        ->with($request)
        ->andReturn([]);

    //
    // Force controller to fail
    //
    $controllerDispatcher->shouldReceive('dispatch')
        ->andThrow(new \RuntimeException('boom'));

    $eventDispatcher->shouldNotReceive('dispatch');

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $kernel->handle($request);

    expect($request->getStartTime())->toBeGreaterThan(0.0);
});

it('10. never returns the original response if ResponseEvent replaces it', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $original = new Response('ORIGINAL', 200);
    $modified = new Response('MODIFIED', 200);

    $resolver->shouldReceive('getMiddlewareForRequest')->andReturn([]);

    $controllerDispatcher->shouldReceive('dispatch')
        ->once()
        ->with($request)
        ->andReturn($original);

    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->with(m::on(function (ResponseEvent $event) use ($modified) {
            $event->setResponse($modified);
            return true;
        }))
        ->andReturnUsing(fn ($e) => $e);

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $result = $kernel->handle($request);

    expect($result)->toBe($modified);
    expect($result)->not()->toBe($original);
});

it('11. kernel lifecycle order is always: middleware → controller → response event → terminate event', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $response = new Response('OK', 200);

    $resolver->shouldReceive('getMiddlewareForRequest')->andReturn([]);

    $controllerDispatcher->shouldReceive('dispatch')
        ->once()
        ->with($request)
        ->andReturn($response);

    $sequence = [];

    $eventDispatcher->shouldReceive('dispatch')
        ->twice()
        ->andReturnUsing(function ($event) use (&$sequence) {
            $sequence[] = $event::class;
            return $event;
        });

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    $result = $kernel->handle($request);
    $kernel->terminate($request, $result);

    expect($sequence)->toBe([
        ResponseEvent::class,
        TerminateEvent::class,
    ]);
});

it('12. never allows terminate() to throw even if listeners fail', function () {
    $resolver            = m::mock(MiddlewareResolverInterface::class);
    $controllerDispatcher = m::mock(ControllerDispatcher::class);
    $eventDispatcher     = m::mock(EventDispatcher::class);

    $request  = makeRequest();
    $response = new Response('OK', 200);

    $request->setStartTime(microtime(true));

    //
    // Event dispatcher throws
    //
    $eventDispatcher->shouldReceive('dispatch')
        ->once()
        ->andThrow(new RuntimeException('listener failed'));

    $kernel = new Kernel(false, $resolver, $controllerDispatcher, $eventDispatcher);

    //
    // If this throws, the test fails
    //
    $kernel->terminate($request, $response);

    expect(true)->toBeTrue(); // reached here = pass
});






