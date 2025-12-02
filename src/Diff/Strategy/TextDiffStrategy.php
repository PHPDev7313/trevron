<?php

namespace JDS\Diff\Strategy;

use JDS\Contracts\Diff\DiffStrategyInterface;

class TextDiffStrategy implements DiffStrategyInterface
{

    /**
     * @inheritDoc
     */
    public function diff(mixed $before, mixed $after): array
    {
        $before = (string)$before;
        $after = (string)$after;

        $beforeLines = explode("\n", $before);
        $afterLines = explode("\n", $after);

        $max = max(count($beforeLines), count($afterLines));
        $changes = [];

        for ($i = 0; $i < $max; $i++) {
            $b = $beforeLines[$i] ?? '';
            $a = $afterLines[$i] ?? '';

            if ($b !== $a) {
                $changes[($i + 1)] = [
                    'before' => $b,
                    'after' => $a,
                    'changed' => true
                ];
            }
        }

        return ['lines' => $changes];
    }
}

