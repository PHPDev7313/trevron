<?php

declare(strict_types=1);

namespace JDS\Bootstrap\Phase;

use JDS\Contracts\Bootstrap\BootstrapPhase;
use JDS\Contracts\Bootstrap\BootstrapInvariantInterface;
use JDS\Contracts\Bootstrap\BootstrapPhaseInterface;
use League\Container\Container;

final class FinalizePhase implements BootstrapPhaseInterface
{
    /** @param list<BootstrapInvariantInterface> $invariants */
    public function __construct(private readonly array $invariants)
    {
    }

    public function phase(): BootstrapPhase
    {
        return BootstrapPhase::FINALIZE;
    }

    public function bootstrap(Container $container): void
    {
        foreach ($this->invariants as $invariant) {
            $invariant->assert($container);
        }
    }
}