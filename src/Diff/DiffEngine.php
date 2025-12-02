<?php

namespace JDS\Diff;

use JDS\Contracts\Diff\DiffStrategyInterface;
use JDS\Exceptions\DiffRunTimeException;

class DiffEngine
{
    /** @var array<string, DiffStrategyInterface> */
    private array $strategies = [];

    public function addStrategy(string $type, DiffStrategyInterface $strategy): void
    {
        $this->strategies[$type] = $strategy;
    }

    public function diff(mixed $before, mixed $after, string $type): array
    {
        if (!isset($this->strategies[$type])) {
            throw new DiffRunTimeException("No strategy registered for diff type '$type'.");
        }
        return $this->strategies[$type]->diff($before, $after);
    }
}

