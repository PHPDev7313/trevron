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

    //
    // NOTE: the entity MUST implement:
    // - public function getEntityIndentifier(): string|ing;
    // - public function getEntityType(): string;
    // - public function toStateArray(): array;

    /**
     * This MUST be implemented by the entity itself.
     * Never implement it here.
     *
     * @return string|int
     */
    abstract public function getEntityIdentifier(): string|int;

    /**
     * This MUST be implemented by the entity itself
     *
     * @return string
     */
    abstract public function getEntityType(): string;

    /**
     * This MUST be implemented by the entity itself
     *
     * @return array
     *
     */
    abstract public function toStateArray(): array;
}

