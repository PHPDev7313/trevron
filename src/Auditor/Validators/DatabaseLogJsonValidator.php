<?php

namespace JDS\Auditor\Validators;

use JDS\Exceptions\Auditor\JsonValidationException;
use JDS\Processing\ErrorProcessor;

class DatabaseLogJsonValidator
{

    /**
     * Validates and encodes the provided data into a JSON string.
     *
     * @param mixed $data The data to be validated and encoded. Can be an array or an object.
     * @return string The JSON-encoded string representation of the input data.
     * @throws JsonValidationException If the data cannot be encoded to JSON.
     */
    public function validateAndEncode(mixed $data): string
    {
        if (is_array($data) || is_object($data)) {
            $encodedJson = json_encode($data);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $encodedJson;
            }
        }
        // if it's not valid JSON, throw an exception
        $exitCode = 214;
        ErrorProcessor::process(
            new JsonValidationException('Provided data cannot be encoded to JSON.'),
            $exitCode,
            "Invalid string|object provided. Cannot encode to JSON."
        );
        exit($exitCode);
    }

    /**
     * Validates and decodes the provided JSON string into a PHP data structure.
     *
     * @param string $jsonString The JSON string to be validated and decoded.
     * @return mixed The decoded PHP data structure, which can be an array or null.
     * @throws JsonValidationException If the JSON string cannot be decoded.
     */
    public function validateAndDecode(string $jsonString): mixed
    {
        $decodedJson = json_decode($jsonString, true);

        // check if decoding was successful
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decodedJson;
        }

        // if it wasn't able to decode, throw an exception
        $exitCode = 216;
        ErrorProcessor::process(
            new JsonValidationException('Provided JSON string cannot be decoded.'),
            $exitCode,
            "Invalid JSON string provided. Cannot decode."
        );
        exit($exitCode);
    }
}