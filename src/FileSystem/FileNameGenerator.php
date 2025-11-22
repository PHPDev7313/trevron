<?php

namespace JDS\FileSystem;

class FileNameGenerator implements FileNameGeneratorInterface
{
    public function generate(string $baseName, string $extension = 'json'): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower($baseName));
        return "{$safe}_" . date('Ymd_His') . ".{$extension}";
    }

    public function makeUnique(string $directory, string $fileName): string
    {
        $path = $directory . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($path)) {
            return $path;
        }

        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $counter = 1;

        do {
            $candidate = "{$base}_{$counter}.{$ext}";
            $path = $directory . DIRECTORY_SEPARATOR . $candidate;
            $counter++;
        } while (file_exists($path));

        return $path;
    }
}

