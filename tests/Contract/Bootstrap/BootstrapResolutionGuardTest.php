<?php

use JDS\Bootstrap\BootstrapContainer;
use JDS\Bootstrap\BootstrapRunner;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use JDS\Exceptions\Bootstrap\BootstrapResolutionNotAllowedException;

it('1. forbids service resolution during bootstrap', function () {
    $container = new BootstrapContainer();

    $runner = new BootstrapRunner($container);

    $runner->addPhase(new class implements \JDS\Contracts\Bootstrap\BootstrapPhaseInterface {
        public function bootstrap($container): void
        {
            // This must fail
            $container->get('anything');
        }
    });

    expect(fn () => $runner->run())
        ->toThrow(BootstrapResolutionNotAllowedException::class);
});

it('2. allows service registration during bootstrap', function () {
    $container = new BootstrapContainer();

    $runner = new BootstrapRunner($container);

    $runner->addPhase(new class implements BootstrapPhaseInterface {
        public function bootstrap($container): void
        {
            $container->add('test', 'value');
        }
    });

    $runner->run();

    // After bootstrap, resolution must work
    expect($container->get('test'))->toBe('value');
});

it('3. forbids getNew() during bootstrap', function () {
    $container = new BootstrapContainer();

    $runner = new BootstrapRunner($container);

    $runner->addPhase(new class implements BootstrapPhaseInterface {
        public function bootstrap($container): void
        {
            $container->getNew('anything');
        }
    });

    expect(fn () => $runner->run())
        ->toThrow(BootstrapResolutionNotAllowedException::class);
});

