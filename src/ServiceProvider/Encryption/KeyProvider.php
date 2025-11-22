<?php

namespace JDS\ServiceProvider\Encryption;

final class KeyProvider
{
    private string $key;

    public function __construct(string $secret)
    {
        // expect binary key (32 bytes) or base64 encoded. Normalize:
        $decoded = base64_decode($secret, true);
        if ($decoded !== false && strlen($decoded) === 32) {
            $this->key = $decoded;
        } else {
            // derive 32 bytes key from raw secret (KDF) -- use hash for simplicity, consider HKDF in real world
            $this->key = hash('sha256', $secret, true);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }
}

