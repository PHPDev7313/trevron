<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonDecoderInterface;

class JsonDecoder implements JsonDecoderInterface
{
    public function decode(string $json, bool $assoc=false): array
    {
        $data = json_decode($json, $assoc);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => json_last_error_msg(),
            ];
        }

        return ['success' => true, 'data' => $data];
    }
}

