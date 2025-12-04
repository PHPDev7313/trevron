<?php

namespace JDS\Security;

use JDS\Contracts\Security\SecretsInterface;

class Secrets implements SecretsInterface
{

    /**
     * @param array<string, mixed> $secrets
     */
    public function __construct(private readonly array $secrets)
    {
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
        return $this->secrets;
    }
}

