<?php

namespace JDS\Contracts\Diff;

interface DiffableEntityInterface
{
    /**
     * Must return a canonical associative array representing current state.
     *
     * Canonical representation of the entity's current state.
     */
    public function toStateArray(): array;

    /**
     * Returns the snapshot taken before persistence.
     *
     * A snapshot of the entity's "before" state, set by mapper/repository.
     */
    public function getOriginalState(): array;

    /**
     * Accepts a snapshot of the original state (usually from repository/mapper).
     *
     * Usually called right after loading from DB, or before mutating.
     */
    public function setOriginalState(array $state): void;

    /**
     * Returns the unique entity ID for audit purposes.
     *
     * Unique identity for the entity - MUST NOT be null.
     */
    public function getEntityIdentifier(): string|int;

    /**
     * Returns a string that defines the type/nature of the entity.
     *
     * Type tag used for logging context, e.g., "role", "user".
     *
     * Example: 'role', 'user', 'permission'
     */
    public function getEntityType(): string;
}

