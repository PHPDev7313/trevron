<?php

use Tests\Stubs\Template\FakeRenderer;

it('1. tracks render calls, template, and context', function () {
    $renderer = new FakeRenderer();

    $output = $renderer->render('example.html.twig', ['foo' => 'bar']);

    expect($renderer->getRenderCount())->toBe(1);
    expect($renderer->getLastTemplate())->toBe('example.html.twig');
    expect($renderer->getLastContext())->toBe(['foo' =>'bar']);

    //
    // Content is deterministic and includes template + context
    //
    expect($output)->toContain('rendered:example.html.twig');
    expect($output)->toContain('"foo":"bar"');
});

it('2. reset render count and last values', function () {
    $renderer = new FakeRenderer();

    $renderer->render('a.html.twig', ['a' => 1]);
    $renderer->render('b.html.twig', ['b' => 2]);

    expect($renderer->getRenderCount())->toBe(2);
    expect($renderer->getLastTemplate())->toBe('b.html.twig');

    $renderer->reset();

    expect($renderer->getRenderCount())->toBe(0);
    expect($renderer->getLastTemplate())->toBeNull();
    expect($renderer->getLastContext())->toBeNull();
});





