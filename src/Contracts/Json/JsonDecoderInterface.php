<?php

namespace JDS\Contracts\Json;

interface JsonDecoderInterface
{
    public function decode(string $json): array;
}

