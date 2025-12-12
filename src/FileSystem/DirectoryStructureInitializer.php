<?php

namespace JDS\FileSystem;

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

class DirectoryStructureInitializer
{
    public function __construct(
        private DirectoryInitializationState $state
    ) {}

    public function initialize(string $basePath, array $structure): void
    {
        if ($this->state->isInitialized()) {
            return; // skip, first-run already handled
        }

        $this->createStructure($basePath, $structure);

        $this->state->markInitialized();
    }

    public function createStructure(string $basePath, array $structure): void
    {
        foreach ($structure as $key => $value) {

            //
            // Resolve directory name
            //
            if (is_array($value)) {
                if (!is_string($key)) {
                    throw new StatusException(
                        StatusCode::FILESYSTEM_INVALID_STRUCTURE,
                        "Invalid directory structure: nested directories require a string key."
                    );
                }

                $dir = $key;
            } else {
                if (!is_string($value)) {
                    throw new StatusException(
                        StatusCode::FILESYSTEM_INVALID_STRUCTURE,
                        "Invalid directory name: expected string."
                    );
                }
                $dir = $value;
            }
            $path = $basePath . DIRECTORY_SEPARATOR . $dir;

            //
            // create directory if missing
            //
            if (!is_dir($path)) {
                if (!@mkdir($path, 0755, true)) {
                    throw new StatusException(
                        StatusCode::FILESYSTEM_DIRECTORY_CREATION_FAILED,
                        "Failed to create directory: {$path}"
                    );
                }
            }

            //
            // Check directory writability
            //
            if (!is_writable($path)) {
                throw new StatusException(
                    StatusCode::FILESYSTEM_DIRECTORY_NOT_WRITABLE,
                    "Directory is not writable: {$path}"
                );
            }

            //
            // Recurse for nested structure
            //
            if (is_array($value)) {
                $this->createStructure($path, $value);
            }
        }
    }
}

