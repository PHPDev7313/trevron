<?php

namespace JDS\ServiceProvider\Encryption;

final class KeyProvider
{
    private string $key;

    public function __construct(string $rawKey)
    {
        // expect binary key (32 bytes) or base64 encoded. Normalize:
        if (base64_decode($rawKey, true) !== false && strlen(base64_decode($rawKey)) === 32) {
            $this->key = base64_decode($rawKey);
        } else {
            // derive 32 bytes key from raw secret (KDF) -- use hash for simplicity, consider HKDF in real world
            $this->key = hash('sha256', $rawKey, true);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }


}

