<?php

namespace JDS\ServiceProvider\Encryption;

final class NonceProvider
{
    public function generate(int $length): string
    {
        return random_bytes($length);
    }
}

