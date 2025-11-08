<?php

namespace JDS\Dbal;

class JSONValidator implements JSONValidatorInterface
{

    /**
     * Validate and clean a JSON string.
     * @param string $jsonString
     * @return array [ 'success' => bool, 'data' => mixed, 'error' => string|null ]
     */
    public static function validate(mixed $jsonString): array
    {
        // Step 1: Trim and check for empty input
        $jsonString = trim($jsonString);
        if (empty($jsonString)) {
            return [
                'success' => false,
                'data' => null,
                'error' => 'Empty JSON input.'
            ];
        }

        // Step 2: Try normal decoding first
        $decoded = json_decode($jsonString, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'success' => true,
                'data' => $decoded,
                'error' => null
            ];
        }

        // Step 3: Attempt to auto-correct common formatting issues
        $corrected = self::attemptCorrection($jsonString);
        $decoded = json_decode($corrected, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return [
                'success' => true,
                'data' => $decoded,
                'error' => null
            ];
        }

        // Step 4: Still invalid
        return [
            'success' => false,
            'data' => null,
            'error' => 'Invalid JSON format: ' . json_last_error_msg()
        ];
    }

    /**
     * Try to fix common JSON format issues.
     * @param string $json
     * @return string
     */
    private static function attemptCorrection($json): string
    {
        // Replace single quotes with double quotes
        $json = preg_replace("/'([^']*)'/", '"$1"', $json);

        // Remove trailing commas before } or ]
        $json = preg_replace('/,\s*([\]}])/', '$1', $json);

        // Add missing quotes around keys if they look like JS objects
        $json = preg_replace('/([{,]\s*)([A-Za-z0-9_]+)\s*:/', '$1"$2":', $json);

        // Fix unescaped double quotes inside values
        $json = preg_replace('/(?<!\\\\)"(?![:,}\\]])/', '\\"', $json);

        return $json;
    }
}

