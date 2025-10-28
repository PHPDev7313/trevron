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
     * @param bool $useTimestamp Append a timestamp for uniqueness
     * @return string Safe filename (e.g. "contact_20251028_153045.json")
     */
    public function generate(?string $baseName = 'file', string $extension = 'json', bool $useTimestamp = true): string
    {
        // Sanitize base name
        $baseName = preg_replace('/[A-Za-z0-9_\-]/', '_', strtolower(trim($baseName)));

        if ($useTimestamp) {
            $timestamp = date('Ymd_His');
            $filename = "{$baseName}_{$timestamp}.{$extension}";
        } else {
            $filename = "{$baseName}.{$extension}";
        }

        return $filename;
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

        while (file_exists($fullPath)) {
            $fileName = "{$baseName}_{$counter}.{$extension}";
            $fullPath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            $counter++;
        }
        return $fullPath;
    }

}

