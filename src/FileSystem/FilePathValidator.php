<?php

namespace JDS\FileSystem;

use JDS\Contracts\FileSystem\FilePathValidatorInterface;

class FilePathValidator implements FilePathValidatorInterface
{
    public function validate(string $path): array
    {
        if (empty($path)) {
            return ['success' => false, 'error' => "Path cannot be empty"];
        }

        $path = str_replace(['\\', '//'], '/', $path);
        $path = rtrim($path, '/');

        $directory = dirname($path);

        if (!is_dir($directory)) {
            if (!@mkdir($directory, 0755, true)) {
                return ['success' => false, 'error' => "Cannot create directory $directory"];
            }
        }

        if (!is_writable($directory)) {
            return ['success' => false, 'error' => "Directory not writable: $directory"];
        }

        return ['success' => true, 'path' => $path];
    }
}

