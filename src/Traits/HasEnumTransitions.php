<?php

namespace JDS\Traits;

use JDS\Contracts\Enum\EnumInterface;
use JDS\Http\InvalidArgumentException;

enum HasEnumTransitions
{

    public static function isValid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }

    public static function fromValue(?string $value): ? self
    {
        if ($value === null) {
            return null;
        }
        return self::tryFrom($value);
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromString(string $status): self
    {
        $instance = self::tryFrom($status);

        if (!$instance) {
            throw new InvalidArgumentException("Invalid status: $status");
        }
        return $instance;
    }

    public function transition(EnumInterface $next, string $reason, string $changedBy): array
    {
        if (!$this->canTransitionTo($next)) {
            throw new InvalidArgumentException("Illegal transition from {$this->value} to {$next->value}");
        }

        return [
            'from' => $this->value,
            'to' => $next->value,
            'reason' => $reason,
            'changed_by' => $changedBy,
            'changed_at' => now()->format("Y-m-d H:i:s"),
        ];
    }
}

