<?php

namespace JDS\Auditor\Provider;

use JDS\Auditor\Exception\InvalidArgumentException;
use JDS\Auditor\Exception\JsonValidationException;
use JDS\Auditor\Exception\ValidLogLevelsException;
use JDS\Auditor\Level;
use JDS\Processing\ErrorProcessor;

class LogLevelProvider
{
    private string $filePath;

    /**
     * Constructor for dependency injection.
     *
     * @param string $filePath Path to the JSON file containing log levels.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Load and normalize valid log levels.
     *
     * @return array Array of validated and normalized levels.
     */
    public function getValidLogLevels(): array
    {
        // Step 1: Load and validate JSON
        $data = $this->loadJsonFile();
        if (empty($data)) {
            $exitCode = 215;
            ErrorProcessor::process(
                new ValidLogLevelsException("There are no valid log levels in the file!"),
                $exitCode,
                "Log levels file is empty!");
            exit($exitCode);
        }
        // Step 2: Validate and normalize levels
        return $this->validateAndNormalizeLevels($data['levels']);
    }

    /**
     * Load and validate the JSON file.
     *
     * @return array Decoded JSON data.
     * @throws ValidLogLevelsException if file loading or decoding fails.
     */
    private function loadJsonFile(): array
    {
        if (!file_exists($this->filePath)) {
            $exitCode = 215;
            ErrorProcessor::process(
                new ValidLogLevelsException("Log levels JSON file not found at: {$this->filePath}"),
            $exitCode,
            "Log levels file not found!");
            exit($exitCode);
        }

        $rawJson = file_get_contents($this->filePath);
        $data = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $exitCode = 211;
            ErrorProcessor::process(
                new JsonValidationException(
                    sprintf("Failed to parse JSON file %s JSON: %s",$this->filePath,json_last_error_msg())),
                    $exitCode,
                    "Log levels file is not valid JSON!"
            );
            exit($exitCode);
        }

        if (!isset($data['levels']) || !is_array($data['levels'])) {
            $exitCode = 212;
            ErrorProcessor::process(
                new InvalidArgumentException(
                sprintf('The JSON file %s must contain a "levels" key with an array.', $this->filePath)),
                $exitCode,
                "The Json File missing critical keys! Contact Support!"
            );
            exit($exitCode);
        }

        return $data;
    }

    /**
     * Validate and normalize log levels using the Level class.
     *
     * @param array $rawLevels Unvalidated levels from the JSON file.
     * @return array Validated and normalized levels.
     */
    private function validateAndNormalizeLevels(array $rawLevels): array
    {
        $validatedLevels = [];

        foreach ($rawLevels as $rawLevel) {
            // Create a Level instance to validate and normalize
            $level = new Level($rawLevel);

            // Store the normalized level
            $validatedLevels[] = (string) $level;
        }

        return $validatedLevels;
    }

}

