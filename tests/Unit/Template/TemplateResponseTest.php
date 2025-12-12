<?php

use JDS\Http\Response;
use JDS\Http\TemplateResponse;
use Tests\Stubs\Template\FakeRenderer;

it('1. renders template using renderer and buffers the result', function () {
    $renderer = new FakeRenderer();

    $response = new TemplateResponse(
        'home.html.twig',
        ['title' => 'Dashboard'],
        200
    );

    $first = $response->render($renderer);
    $second = $response->render($renderer);

    //
    // Same content
    //
    expect($second)->toBe($first);

    //
    // Renderer only called once
    //
    expect($renderer->getRenderCount())->toBe(1);

    //
    // Last render info captured correctly
    //
    expect($renderer->getLastTemplate())->toBe('home.html.twig');
    expect($renderer->getLastContext())->toMatchArray(['title' => 'Dashboard']);
});

it('2. converts to Response with correct content, status, and headers', function () {
    $renderer = new FakeRenderer();

    $template = new TemplateResponse(
        'page.html.twig',
        ['foo' => 'bar'],
        201,
        ['X-Test' => 'yes']
    );

    $response = $template->toResponse($renderer);

    //
    // Instance check
    //
    expect($response)->toBeInstanceOf(Response::class);

    //
    // Status
    //
    expect($response->getStatus())->toBe(201);

    //
    // Headers: include custom + default Content type
    //
    $headers = $response->getHeaders();
    expect($headers)->toHaveKey('X-Test', 'yes');
    expect($headers)->toHaveKey('Content-Type');

    //
    // Content comes from renderer
    //
    $content = $response->getContent();
    expect($content)->not()->toBeNull();
    expect($content)->toContain('rendered:page.html.twig');
});

it('3. does not override an explicitly provided Content-Type header', function () {
    $renderer = new FakeRenderer();

    $template = new TemplateResponse(
        'json.html.twig',
        ['foo' => 'bar'],
        200,
        ['Content-Type' => 'application/json']
    );

    $response = $template->toResponse($renderer);

    $headers = $response->getHeaders();
    expect($headers)->toHaveKey('Content-Type', 'application/json');
});

it('4. withContext replaces the entire context and resets the render buffer', function () {
    $renderer  = new FakeRenderer();

    $original = new TemplateResponse(
        'view.html.twig',
        ['a' => 1],
        200
    );

    $first = $original->render($renderer);

    //
    // Replace context
    //
    $modified = $original->withContext(['b' => 2]);

    //
    // Ensure immutability
    //
    expect($modified)->not()->toBe($original);

    $renderer->reset();
    $second = $modified->render($renderer);

    //
    // FakeRenderer called once (after reset)
    //
    expect($renderer->getRenderCount())->toBe(1);

    //
    // Content differs because context changed
    //
    expect($second)->not()->toBe($first);

    //
    // Original context unchanged
    //
    expect($original->getContext())->toMatchArray(['a' => 1]);

    //
    // New context applied
    //
    expect($modified->getContext())->toMatchArray(['b' => 2]);
});

it('5. withAddedContext merges context and resest the render buffer', function () {
    $renderer = new FakeRenderer();

    $original = new TemplateResponse(
        'view.html.twig',
        ['a' => 1],
        200
    );

    $original->render($renderer);

    $renderer->reset();
    $modified = $original->withAddedContext(['b' => 2]);

    expect($modified)->not()->toBe($original);

    $content = $modified->render($renderer);

    //
    // FakeRenderer hit once after reset
    //
    expect($renderer->getRenderCount())->toBe(1);

    //
    // Content encodes merged context
    //
    expect($content)->toContain('"a":1');
    expect($content)->toContain('"b":2');
});

it('6. withStatusCode returns a new instance with updated status', function () {
    $renderer = new FakeRenderer();

    $original = new TemplateResponse(
        'view.html.twig',
        ['a' => 1],
        200
    );

    $updated = $original->withStatusCode(404);

    //
    // Immutability
    //
    expect($updated)->not()->toBe($original);

    //
    // New status applied
    //
    $response = $updated->toResponse($renderer);
    expect($response->getStatus())->toBe(404);

    //
    // Original status unchanged
    //
    $originalResponse = $original->toResponse($renderer);
    expect($originalResponse->getStatus())->toBe(200);
});

it('7. withHeader adds or overrides headers immutably', function () {
    $renderer = new FakeRenderer();

    $original = new TemplateResponse(
        'view.html.twig',
        ['a' => 1],
        200,
        ['X-Original' => 'one']
    );

    $updated = $original->withHeader('X-Original', 'two');

    expect($updated)->not()->toBe($original);

    $origHeaders = $original->getHeaders();
    $updateHeaders = $updated->getHeaders();

    expect($origHeaders)->toHaveKey('X-Original', 'one');
    expect($updateHeaders)->toHaveKey('X-Original', 'two');
});

it('8. reuses buffered content between render() and toResponse()', function () {
    $renderer = new FakeRenderer();

    $template = new TemplateResponse(
        'view.html.twig',
        ['a' => 1],
        200,
    );

    $first = $template->render($renderer);
    $response = $template->toResponse($renderer);
    $second = $response->getContent();

    //
    // Same content
    //
    expect($second)->toBe($first);

    //
    // Only one renderer call total
    //
    expect($renderer->getRenderCount())->toBe(1);
});





