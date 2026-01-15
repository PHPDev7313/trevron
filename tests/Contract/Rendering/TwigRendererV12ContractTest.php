<?php


use JDS\Configuration\Config;
use JDS\Contracts\Rendering\RendererInterface;
use JDS\ServiceProvider\TwigRendererServiceProvider;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;

it('1. [v1.2 FINAL] TwigRenderer renders a template successfully', function () {
    // --------------------------------------------------
    // Arrange: minimal config
    // --------------------------------------------------
    $basePath = realpath(__DIR__ . '/Fixtures');

    $config = [
        'app' => [
            'basePath' => $basePath,
            'environment' => 'development',
        ],
        'twig' => [
            'templates' => [
                'paths' => ['templates'],
            ],
        ],
    ];

    $container = new Container();
    $container->add(Config::class)
        ->addArgument(new ArrayArgument($config))
        ->setShared(true);

    // ------------------------------------------------
    // Act: register provider
    // ------------------------------------------------
    (new TwigRendererServiceProvider())->register($container);

    /** @var RendererInterface $renderer */
    $renderer = $container->get(RendererInterface::class);

    // ----------------------------------------------------
    // Assert
    // ----------------------------------------------------
    $output = $renderer->render('hello.html.twig', ['name' => 'World']);

    expect($output)->toContain('Hello World!');
//    expect($output)->toContain('/assets/logo.png');
});

it('2.1. [v1.2 FINAL] TwigRenderer fails if twig.templates.paths is missing', function () {
    $basePath = realpath(__DIR__ . '/Fixtures');

    $container = new Container();
    $config = [
        'app' => [
            'basePath' => $basePath,
            'environment' => 'development',
        ],
    ];

    $container->add(Config::class)
        ->addArgument(new ArrayArgument($config))
        ->setShared(true);

    expect(fn () => (new TwigRendererServiceProvider())->register($container))->toThrow(RuntimeException::class, 'No twig template paths configured. [Config].');
});

it('2.2. [v1.2 FINAL] TwigRenderer fails if twig.templates paths is empty', function () {
    $basePath = realpath(__DIR__ . '/Fixtures');
    $container = new Container();
    $config = [
        'app' => [
            'basePath' => $basePath,
            'environment' => 'development',
            ],
        'twig' => [
            'templates' => [
                'paths' => [],
            ],
        ],
    ];
    $container->add(Config::class)
        ->addArgument(new ArrayArgument($config))
        ->setShared(true);

    expect(fn () => (new TwigRendererServiceProvider())->register($container))->toThrow(RuntimeException::class, 'No twig template paths configured. [Config].');
});

it('2.3. [v1.2 FINAL] TwigRenderer fails if template directory does not exist', function () {
    $basePath = realpath(__DIR__ . '/Fixtures');

    $container = new Container();
    $config = [
        'app' => [
            'basePath' => $basePath,
            'environment' => 'development',
        ],
        'twig' => [
            'templates' => [
                'paths' => ['this-directory-does-not-exist'],
            ],
        ],
    ];
    $container->add(Config::class)
        ->addArgument(new ArrayArgument($config))
        ->setShared(true);

    $templateDir = $container->get(Config::class)->twigTemplateRoot();
    $templatePath = $basePath . '/' . $templateDir;
    $templatePath = str_replace('\\', '/', $templatePath);

        expect(fn () =>
    (new TwigRendererServiceProvider())->register($container)
    )->toThrow(RuntimeException::class, "Twig templates path is invalid or does not exist: {$templatePath}. [Twig:Renderer:Service:Provider].");
});

it('2.4. [V1.2 FINAL] TwigRenderer fails if app.basePath is missing', function () {

    $container = new Container();
    $config = [
        'twig' => [
            'templates' => [
                'paths' => ['templates'],
            ],
        ],
    ];

    $container->add(Config::class)
        ->addArgument(new ArrayArgument($config))
        ->setShared(true);

    expect(fn () => (new TwigRendererServiceProvider())->register($container))->toThrow(RuntimeException::class);
});


