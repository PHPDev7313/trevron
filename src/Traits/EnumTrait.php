<?php

namespace JDS\Traits;

use JDS\Http\InvalidArgumentException;

/**
 * Trait EnumTrait
 * Provides common helper methods for string-backed PHP enums.
 *
 * Usage:
 *    enum MyEnum: string implements EnumInterface { use EnumTrait; ... }
 */
trait EnumTrait
{
    public static function isValid(string $value): bool
    {
        return (static::tryFrom($value) !== null);
    }

    public static function fromValue(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }
        return static::tryFrom($value);
    }

    public static function all(): array
    {
        return array_map(fn($case) => $case->value, static::cases());
    }

    public function value(): string
    {
        // backed enum ensures $this->value exists
        return $this->value;
    }

    public static function fromString(string $status): self
    {
        $inst = static::tryFrom($status);
        if ($inst === null) {
            throw new InvalidArgumentException("Invalid enum value: $status");
        }
        return $inst;
    }
}

