<?php

namespace JDS\Contracts\Json;

interface JsonEncoderInterface
{
    public function encode($data): array;
}

