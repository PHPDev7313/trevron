<?php

namespace JDS\Parsing;

use JDS\Contracts\Parsing\JsonEncoderInterface;

class JsonEncoder implements JsonEncoderInterface
{
    public function encode(string $json): string
    {
        $encoded = json_encode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonParsingException(json_last_error_msg(), json_last_error());
        }
        return $encoded;
    }
}

