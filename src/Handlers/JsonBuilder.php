<?php

namespace JDS\Handlers;

class JsonBuilder implements BuilderInterface
{

    public function __construct(private string $filePath)
    {
    }

    /**
     * Build and validate JSON from mixed input
     *
     * @param mixed $data Array, object, or JSON string
     * @return array ['success' => bool, 'data' => string|null, 'error' => string|null ]
     */
    public function build($data): array
    {

        // 1. Validate input type
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'data' => null,
                    'error' => 'Invalid JSON string: ' . json_last_error_msg()
                ];
            }
            $data = $decoded;
        } elseif (!is_array($data) && !is_object($data)) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Invalid input type: ' . gettype($data) . '. Must be an array, object, or valid JSON string.'
            ];
        }

        // 2. Validate for resource types (non-JSON-safe)
        foreach ($data as $key => $value) {
            if (is_resource($value)) {
                return [
                    'success' => false,
                    'data' => null,
                    'error' => "Invalid value for key '{$key}': resource type not supported"
                ];
            }
        }

        // 3. Encode JSON
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'JSON encoding failed: ' . json_last_error_msg()
            ];
        }
        return [
            'success' => true,
            'data' => $json,
            'error' => null
        ];
    }

    /**
     * Save JSON data to a file
     *
     * @param mixed $data Array, object, or JSON string
     * @return array ['success' => bool, 'error' => string|null ]
     */
    public function saveToFile(mixed $data): array
    {
        $result = $this->build($data);
        if (!$result['success']) {
            return $result;
        }

        try {
            file_put_contents($this->filePath, $result['data']);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => "Failed to save file: " . $e->getMessage()
            ];
        }

        return [
            'success' => true,
            'error' => null
        ];
    }
}

