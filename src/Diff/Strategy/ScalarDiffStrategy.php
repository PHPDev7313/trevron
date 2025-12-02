<?php

namespace JDS\Diff\Strategy;

use JDS\Contracts\Diff\DiffStrategyInterface;

class ScalarDiffStrategy implements DiffStrategyInterface
{

    /**
     * @inheritDoc
     */
    public function diff(mixed $before, mixed $after): array
    {
        return [
            'value' => [
                'before' => $before,
                'after' => $after,
                'changed' => $before !== $after
            ]
        ];
    }
}

