<?php

namespace JDS\ServiceProvider\Encryption;

final class NonceProvider
{
    public function generate(int $length=24): string
    {
        return random_bytes($length);
    }
}

