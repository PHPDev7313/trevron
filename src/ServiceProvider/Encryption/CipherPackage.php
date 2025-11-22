<?php

namespace JDS\ServiceProvider\Encryption;

final class CipherPackage
{
    public string $version = "1";

    public function __construct(
        public string $alg,
        public string $nonce,  // raw bytes
        public string $ciphertext, // raw bytes
        public ?string $aad = null
    )
    {
    }
}


