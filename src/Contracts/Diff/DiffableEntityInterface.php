<?php

namespace JDS\Contracts\Diff;

interface DiffableEntityInterface
{
    /**
     * Must return a canonical associative array representing current state.
     */
    public function toStateArray(): array;

    /**
     * Returns the snapshot taken before persistence.
     */
    public function getOriginalState(): array;

    /**
     * Accepts a snapshot of the original state (usually from repository/mapper).
     */
    public function setOriginalState(array $state): void;

    /**
     * Returns the unique entity ID for audit purposes.
     */
    public function getEntityIdentifier(): string|int;

    /**
     * Returns a string that defines the type/nature of the entity.
     *
     * Example: 'role', 'user', 'permission'
     */
    public function getEntityType(): string;
}

