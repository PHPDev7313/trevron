<?php

namespace JDS\Diff\Strategy;

use JDS\Contracts\Diff\DiffStrategyInterface;

class ArrayDiffStrategy implements DiffStrategyInterface
{

    /**
     * @inheritDoc
     */
    public function diff(mixed $before, mixed $after): array
    {
        $before = (array)$before;
        $after = (array)$after;

        $keys = array_unique(array_merge(
            array_keys($before),
            array_keys($after)
        ));

        $diff = [];

        foreach ($keys as $key) {
            $b = $before[$key] ?? null;
            $a = $after[$key] ?? null;

            $diff[$key] = [
                'before' => $b,
                'after' => $a,
                'changed' => $b !== $a
            ];
        }

        return $diff;
    }
}

