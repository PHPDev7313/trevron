<?php

namespace JDS\Security;

use JDS\Exceptions\CryptoRuntimeException;

class SecretsManager
{
    public function __construct(
        private readonly string $encryptedFilePath,
        private readonly SecretsCrypto $crypto
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        if (!is_file($this->encryptedFilePath)) {
            throw new CryptoRuntimeException("Encrypted secrets file not found: {$this->encryptedFilePath}");
        }

        $encoded = file_get_contents($this->encryptedFilePath);
        if ($encoded === false) {
            throw new CryptoRuntimeException("Unable to read encrypted secrets file: {$this->encryptedFilePath}");
        }

        $json = $this->crypto->decryptString($encoded);

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new CryptoRuntimeException("Decrypted secrets file is not valid JSON");
        }

        return $data;
    }

    /**
     * Encrypt and save secrets array
     */
    public function save(array $secrets): void
    {
        $json = json_encode($secrets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new CryptoRuntimeException("Failed to encode secrets as JSON");
        }

        $encoded = $this->crypto->encryptString($json);

        if (file_put_contents($this->encryptedFilePath, $encoded) === false) {
            throw new CryptoRuntimeException("Failed to write encrypted secrets file: {$this->encryptedFilePath}");
        }
    }
}

