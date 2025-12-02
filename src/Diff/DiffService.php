<?php

namespace JDS\Diff;

use JDS\Contracts\Diff\DiffServiceInterface;
use JDS\Contracts\Diff\DiffStrategyInterface;

class DiffService implements DiffServiceInterface
{
    public function __construct(
        private DiffStrategyInterface $strategy
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function diff(array $before, array $after, string $context = null): array
    {
        //
        // In future you can switch based on context.
        //
        return $this->strategy->diff($before, $after);
    }
}

