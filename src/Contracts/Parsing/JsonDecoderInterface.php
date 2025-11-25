<?php

namespace JDS\Contracts\Parsing;

interface JsonDecoderInterface
{
    public function decode(string $json): mixed;
}

