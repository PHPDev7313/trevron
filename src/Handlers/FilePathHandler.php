<?php

namespace JDS\Handlers;

class FilePathHandler implements FilePathInterface
{


    /**
     * Validate and prepare the directory for a given file path.
     *
     * @param string $filePath the full file path (e.g. /var/www/data/contacts.json)
     * @return array [ 'success' => bool, 'path' => string|null, 'error' => string|null ]
     */
    public function validatePath(string $filePath): array
    {

        // 1. Basic sanity check
        if (empty($filePath)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File path cannot be empty.'
            ];
        }

        // 2. Normalize path (remove weird slashes)
        $normalizedPath = rtrim(str_replace(['\\', '//'], '/', $filePath), "/");

        // 3. Extract directory path
        $directory = dirname($normalizedPath);

        // 4. Check if directory exists, if not, attempt to create it
        if (!is_dir($directory)) {
            if (!@@mkdir($directory, 0755, true)) {
                return [
                    'success' => false,
                    'path' => null,
                    'error' => "Cannot create directory: {$directory}"
                ];
            }
        }

        // 5. Check directory permissions
        if (!is_writable($directory)) {
            return [
                'success' => false,
                'path' => null,
                'error' => "File path is not writable: {$directory}"
            ];
        }

        // 6. All good - return the normalized path
        return [
            'success' => true,
            'path' => $normalizedPath,
            'error' => null
        ];
    }

    /**
     * Validate only a directory path (for cases where no file name is given)
     *
     * @param string $directory
     * @return array [ 'success' => bool, 'path' => string|null, 'error' => string|null ]
     */
    public function validateDirectory(string $directory): array
    {
        if (empty($directory)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Directory path cannot be empty.'
            ];
        }

        $normalizedDir = rtrim(str_replace(["\\", "//"], "/", $directory), "/");

        if (!is_dir($normalizedDir)) {
            if (!@@mkdir($normalizedDir, 0755, true)) {
                return [
                    'success' => false,
                    'path' => null,
                    'error' => "Failed to create directory: {$normalizedDir}"
                ];
            }
        }

        if (!is_writable($normalizedDir)) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Directory not writable: {$normalizedDir}"
            ];
        }

        return [
            'success' => true,
            'path' => $normalizedDir,
            'error' => null
        ];
    }
}

