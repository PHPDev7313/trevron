<?php

namespace JDS\FileSystem;

class FileDeleter implements FileDeleterInterface
{
    public function delete(string $path): array
    {
        if (!file_exists($path)) {
            return [
                'success' => false,
                'error' => 'File does not exist!'
            ];
        }

        if (!unlink($path)) {
            return [
                'success' => false,
                'error' => 'Unable to remove file!'
            ];
        }
        return [
            'success' => true,
            'message' => 'File has been removed!'
            ];
    }
}

