<?php

namespace JDS\FileSystem;

use JDS\Contracts\FileSystem\FileReaderInterface;
use JDS\Http\FileNotFoundException;

class FileReader implements FileReaderInterface
{
    public function read(string $path): string
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException("File does not exist: $path");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new FileReadException("Failed to read file: $path");
        }
        return $content;
    }
}

