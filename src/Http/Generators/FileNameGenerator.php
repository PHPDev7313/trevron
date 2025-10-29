<?php

namespace JDS\Http\Generators;

use Mockery\Generator\Generator;

class FileNameGenerator implements FileNameGeneratorInterface
{
    /**
     * Generate a safe and unique filename
     *
     * @param string|null $baseName Optional base name (e.g. "contact")
     * @param string $extension File extension without dot (default 'json')
     * @param string |null $directory Optional directory to check for existing files
     * @param bool $useTimestamp Append a timestamp for uniqueness
     * @return string Full path if $directory provided, otherwise filename only (e.g. "contact_20251028_153045.json")
     */
    public function generate(?string $baseName = 'file', string $extension = 'json', ?string $directory=null,  bool $useTimestamp = true): string
    {
        // 1. Sanitize base name
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', strtolower(trim($baseName)));

        // 2. Build the initial file name
        $fileName = $useTimestamp ? "{$baseName}_" . date('Ymd_His') . ".{$extension}" : "{$baseName}.{$extension}";

        // 3. If a directory is provided, check if the file already exists
        if ($directory) {
            $directory = rtrim($directory, DIRECTORY_SEPARATOR);
            $fullPath = $directory . DIRECTORY_SEPARATOR . $fileName;

            if (file_exists($fullPath)) {
                // File already exists - generate a unique one
                return $this->generateUnique($directory, $baseName, $extension);
            }
            return $fullPath;
        }

        // 4. If no directory given, just return the filename
        return $fileName;
    }

    /**
     * Generate a unique filename if one already exists in the directory
     *
     * @param string $directory Directory path
     * @param string|null $baseName
     * @param string $extension
     * @return string Full safe path to a non-existing file
     */
    public function generateUnique(string $directory, ?string $baseName = 'file', string $extension = 'json'): string
    {
        $counter = 1;
        $fileName = $this->generate($baseName, $extension, false);
        $fullPath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        do {
            $fileName = "{$baseName}_{$counter}.{$extension}";
            $fullPath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            $counter++;
        } while (file_exists($fullPath));
        return $fullPath;
    }
}

