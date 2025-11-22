<?php

namespace JDS\ServiceProvider\Encryption;

final class Decryptor
{
    public function __construct(
        private KeyProvider $keys
    )
    {
    }

    public function decrypt(CipherPackage $pkg): string
    {
        $key = $this->keys->getKey();
        if ($pkg->alg === 'xchacha20poly1305' && function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_decrypt')) {
            $plaintext = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
                $pkg->ciphertext,
                $pkg->aad ?? '',
                $pkg->nonce,
                $key
            );
            if ($plaintext === false) {
                throw new CipherRuntimeException('Decryption failed (auth).');
            }
            return $plaintext;
        }

        // AES-256-GCM
        if ($pkg->alg === 'aes-256-gcm') {
            // blob is iv + ciphertext + tag - we must split
            $ivLen = openssl_cipher_iv_length('aes-256-gcm');
            $iv = substr($pkg->ciphertext, 0, $ivLen);
            $tag = substr($pkg->ciphertext, -16); // tag is 16 bytes
            $data = substr($pkg->ciphertext, $ivLen, -16);
            $plain = openssl_decrypt(
                $data,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                $pkg->aad ?? ''
            );
            if ($plain === false) {
                throw new CipherRuntimeException('Decryption failed (openssl/aes).');
            }
            return $plain;
        }
        throw new CipherRuntimeException('Unsupported algorithm or missing extension.');
    }
}

