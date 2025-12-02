<?php

namespace JDS\Diff;

trait DiffableEntityTrait
{
    private array $originalState = [];

    public function setOriginalState(array $state): void
    {
        $this->originalState = $state;
    }

    public function getOriginalState(): array
    {
        return $this->originalState;
    }

    public function getEntityIdentifer(): string|int
    {
        return $this->getId() ?? $this->id ?? null;
    }
}

