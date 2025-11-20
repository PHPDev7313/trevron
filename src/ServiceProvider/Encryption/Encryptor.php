<?php

namespace JDS\ServiceProvider\Encryption;

final class Encryptor
{
    public function __construct(
        private KeyProvider $keys,
        private NonceProvider $nonces
    )
    {
    }

    public function encrypt(string $plaintext, ?string $aad=null): CipherPackage
    {
        // try Sodium AEAD XChaCha20-Poly1305 (preferred)
        if (function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')) {
            $key = $this->keys->getKey();
            $nonce = $this->nonces->generate(24); // XChaCha nonce length
            $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
                $plaintext,
                $aad ?? '',
                $nonce,
                $key
            );
            return new CipherPackage('xchacha20poly1305', $nonce, $ciphertext, $aad);
        }

        // Fallback; AES-256-GCM using OpenSSl
        if (function_exists('openssl_encrypt')) {
            $key = $this->keys->getKey();
            $ivLen = openssl_cipher_iv_length('aes-256-gcm');
            $iv = random_bytes($ivLen);
            $tag = '';
            $ciphertext_raw = openssl_encrypt(
                $plaintext,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                $aad ?? '',
                16
            );
            // store iv + ciphertext + tag in ciphertext blob
            $blob = $iv . $ciphertext_raw . $tag;
            return new CipherPackage('aes-256-gcm', $iv, $blob, $aad);
        }
        throw new CipherRuntimeException('No supported encryption available (sodium or openssl required).');
    }
}

