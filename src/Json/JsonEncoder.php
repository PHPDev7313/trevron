<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonEncoderInterface;

class JsonEncoder implements JsonEncoderInterface
{
    public function encode($data): array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'error' => 'Invalid JSON string: ' . json_last_error_msg()];
            }
            $data = $decoded;
        }

        if (!is_array($data) && !is_object($data)) {
            return ['success' => false, 'error' => 'Data must be an array or JSON string.'];
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Encoding error: ' . json_last_error_msg()];
        }

        return ['success' => true, 'json' => $json];
    }
}

