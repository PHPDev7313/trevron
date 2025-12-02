<?php

namespace JDS\Contracts\Diff;

interface DiffServiceInterface
{
    /**
     * Computes a normalized diff between two entity states.
     *
     * @param array  $before The original entity state.
     * @param array  $after The new entity state.
     * @param string $context Optional context (e.g. entity type).
     *
     * @return array The computed diff; empty array means no changes
     */
    public function diff(array $before, array $after, string $context = ''): array;
}

