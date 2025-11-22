<?php

namespace JDS\Parsing;

interface JsonEncoderInterface
{
    public function encode(string $json): mixed;
}

