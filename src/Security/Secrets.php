<?php

namespace JDS\Security;

use JDS\Contracts\Security\SecretsInterface;

final class Secrets implements SecretsInterface
{

    /**
     * @param array<string, mixed> $secrets
     */
    private readonly array $secrets;

    public function __construct(array $secrets)
    {
        // Defensive deep copy to prevent reference leaks
        $this->secrets = self::deepCopy($secrets);
    }

    /**
     * @inheritDoc
     */
    public function get(string $path, mixed $default = null): mixed
    {
        $segments = explode('.', $path);
        $value = $this->secrets;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return self::deepCopy($this->secrets);
    }

    private static function deepCopy(array $array): array
    {
        return unserialize(serialize($array), ['allowed_classes' => false]);
    }

    public function has(string $path): bool
    {
        return $this->get($path, '__missing__') !== '__missing__';
    }
}

