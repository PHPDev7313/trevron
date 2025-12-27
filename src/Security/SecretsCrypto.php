<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 27, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: BootstrapLifecycleAndInvariants.v1.2.FINAL.md
 */

namespace JDS\Security;

use JDS\Exceptions\CryptoRuntimeException;

class SecretsCrypto
{
    /**
     * @param string $keyRaw Binary key (not hex/base64). Must be SODIUM_CRYPTO_SECRETBOX_KEYBYTES bytes.
     */
    public function __construct(private readonly string $keyRaw)
    {
        if (strlen($this->keyRaw) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new CryptoRuntimeException("Invalid secret key length for SecretsCrypto");
        }
    }

    public static function fromBase64(string $base64): self
    {
        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            throw new CryptoRuntimeException("Invalid base64 for APP_SECRET_KEY");
        }

        return new self($decoded);
    }

    public static function fromHex(string $hex): self
    {
        $decoded = sodium_hex2bin($hex);
        if ($decoded === false) {
            throw new CryptoRuntimeException("Invalid hex for APP_SECRET_KEY");
        }

        return new self($decoded);
    }

    public function encryptString(string $plainText): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plainText, $nonce, $this->keyRaw);

        //
        // store nonce + ciphertext together, base64-encoded
        //
        return base64_encode($nonce . $ciphertext);
    }

    public function decryptString(string $encoded): string
    {
        $raw = base64_decode($encoded, true);
        if ($raw === false) {
            throw new CryptoRuntimeException("Invalid base64 ciphertext for secrets file");
        }

        $nonceSize = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
        if (strlen($raw) < $nonceSize) {
            throw new CryptoRuntimeException("Ciphertext too short for secrets file");
        }

        $nonce = substr($raw, 0, $nonceSize);
        $ciphertext = substr($raw, $nonceSize);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->keyRaw);
        if ($plaintext === false) {
            throw new CryptoRuntimeException("Failed to decrypt secrets file");
        }

        return $plaintext;
    }
}

