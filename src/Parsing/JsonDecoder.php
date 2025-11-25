<?php

namespace JDS\Parsing;

use JDS\Contracts\Parsing\JsonDecoderInterface;

class JsonDecoder implements JsonDecoderInterface
{
    public function decode(string $json): mixed
    {
        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonParsingException("Invalid JSON: " . json_last_error_msg());
        }
        return $decoded;
    }
}

