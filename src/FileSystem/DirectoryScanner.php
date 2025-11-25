<?php

namespace JDS\FileSystem;

use JDS\Contracts\FileSystem\DirectoryScannerInterface;
use JDS\Http\HttpRuntimeException;

class DirectoryScanner implements DirectoryScannerInterface
{
    public function __construct(private string $directory)
    {
    }

    public function getFiles(): array
    {
        if (!is_dir($this->directory)) {
            throw new HttpRuntimeException("Directory not found: {$this->directory}");
        }

        try {
            $files = scandir($this->directory);
            return array_values(
                array_filter($files, fn($f) => !in_array($f, ['.', '..']))
            );
        } catch (\Throwable $e) {
            throw new HttpRuntimeException("Unable to scan directory: {$e->getMessage()}", 0, $e);
        }
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}

