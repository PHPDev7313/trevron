<?php

namespace JDS\FileSystem;

use JDS\Contracts\FileSystem\FileListerInterface;

class FileLister implements FileListerInterface
{

    public function list(string $directory, string $extension = 'json'): array
    {
        if (!is_dir($directory)) {
            return ['success' => false, 'error' => "'Directory not found: $directory"];
        }

        $pattern = rtrim($directory, DIRECTORY_SEPARATOR) . "/*.$extension";

        $files = glob($pattern) ?: [];

        return ["success" => true, "files" => $files];
    }
}