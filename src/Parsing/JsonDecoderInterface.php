<?php

namespace JDS\Parsing;

interface JsonDecoderInterface
{
    public function decode(string $json): mixed;
}

