<?php

namespace JDS\Contracts\Enum;

use BackedEnum;
use JDS\Http\InvalidArgumentException;

interface EnumInterface extends BackedEnum
{

    /**
     * Return the native backed value (string)
     *
     * @return string
     */
    public function value(): string;

    /**
     * Validate if the provided value is a valid enum value.
     *
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool;

    /**
     * Get an enum instance from a string value.
     *
     * Returns null if the provided value is not valid.
     *
     * @param string|null $value
     * @return self|null
     */
    public static function fromValue(?string $value): ?self;

    /**
     * Get a list of all possible values.
     *
     * @return array
     */
    public static function all(): array;

    /**
     * Human-readable label for each job status.
     *
     * @return string
     */
    public function label(): string;

    /**
     * Determine if the job can be verified (typically after admin review).
     *
     * @return bool
     */
    public function canBeVerified(): bool;

    /**
     * Define allowed transitions between statuses.
     *
     * @param self $next
     * @return bool
     */
    public function canTransitionTo(self $next): bool;

    /**
     * Handle transition logic with audit metadata.
     *
     * @param self $next
     * @param string $reason - Why the transition occurred
     * @param string $changeBy - User or system actor performing the change
     * @return array - Structured audit log entry
     * @throws InvalidArgumentException
     */
    public function transition(self $next, string $reason, string $changeBy): array;

    /**
     * Determine if the job can be revoked (e.g., approval reverted).
     *
     * @return bool
     */
    public function isRevocable(): bool;

    /**
     * Parse a string into a valid JobStatus enum, throwing an exception if invalid.
     *
     * @param string $status
     * @return self
     * @throws InvalidArgumentException
     */
    public static function fromString(string $status): self;
}

