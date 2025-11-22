<?php

namespace JDS\FileSystem;

use JDS\Http\FileNotFoundException;

class FileDeleter implements FileDeleterInterface
{
    public function delete(string $path): void
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException("File not found: $path");
        }

        if (!unlink($path)) {
            throw new FileDeleterException("Unable to delete file: $path");
        }
    }
}

