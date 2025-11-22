<?php

namespace JDS\Json;

interface JsonEncoderInterface
{
    public function encode($data): array;
}

