<?php

namespace JDS\Contracts\Diff;

interface DiffStrategyInterface
{
    /**
     * Returns a structured diff array:
     *
     * [
     *
     *      $field => [
     *
     *          'before'  => mixed,
     *
     *          'after'   => mixed,
     *
     *          'changed' => bool
     *
     *      ]
     *
     * ]
     *
     * @param mixed $before
     * @param mixed $after
     *
     * @return array<string, mixed>
     */
    public function diff(mixed $before, mixed $after): array;
}

