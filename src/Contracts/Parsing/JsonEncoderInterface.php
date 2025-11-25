<?php

namespace JDS\Contracts\Parsing;

interface JsonEncoderInterface
{
    public function encode(string $json): mixed;
}

