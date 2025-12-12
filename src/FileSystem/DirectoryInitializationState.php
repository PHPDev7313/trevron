<?php

namespace JDS\FileSystem;

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

class DirectoryInitializationState
{
    public function __construct(
        private string $stateFile
    ) {}

    public function isInitialized(): bool
    {
        if (!is_file($this->stateFile)) {
            return false;
        }

        $contents = @file_get_contents($this->stateFile);

        if ($contents === false) {
            throw new StatusException(
                StatusCode::JSON_FILE_READ_ERROR,
                "Unable to read initialization state file: {$this->stateFile}"
            );
        }

        $json = json_decode($contents, true);

        if (Json_last_error() !== JSON_ERROR_NONE) {
            throw new StatusException(
                StatusCode::JSON_DECODING_FAILED,
                "JSON decode error in initialization state: " . json_last_error_msg()
            );
        }

        return (isset($json['initialized']) && $json['initialized'] === true);
    }

    public function markInitialized(): void
    {

        $dir = dirname($this->stateFile);

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                throw new StatusException(
                    StatusCode::FILESYSTEM_DIRECTORY_CREATION_FAILED,
                    "Failed to create initialization directory: {$dir}"
                );
            }
        }


        $data = [
            'initialized' => true,
            'timestamp' => date("c"),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT);

        if ($json === false) {
            throw new StatusException(
                StatusCode::JSON_ENCODING_FAILED,
                "Failed to encode initialization state to JSON: " . json_last_error_msg()
            );
        }

        //
        // Attempt to write the file
        //

        if (@file_put_contents($this->stateFile,$json ) === false) { // , LOCK_EX
            throw new StatusException(
                StatusCode::FILESYSTEM_STATE_FILE_WRITE_FAILED,
                "Failed to write initialization state file: {$this->stateFile}"
            );
        }
    }
}

