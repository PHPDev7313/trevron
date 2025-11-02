<?php

namespace JDS\Handlers;

use JDS\Http\HttpRuntimeException;

class FileReaderHandler
{

    public function __construct(private string $directory)
    {
    }


    /**
     * @return array ['success' => bool, 'filename' => string|null, 'content' => mixed, 'error' => string|null ]
     */
    public function getFilesData(): array
    {
        $data = [];

        if (!is_dir($this->directory)) {
            $data[] = [
                'success' => false,
                'filename' => null,
                'content' => null,
                'error' => "Directory '{$this->directory}' does not exist!"
            ];
            return $data;
        }

        /**
         * glob returns array | false
         */
        $files = glob($this->directory . '/*.json');
        if (is_array($files)) {
            foreach ($files as $file) {
                $contents = file_get_contents($file);
                $decoded = json_decode($contents, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $data[] = [
                        'success' => false,
                        'filename' => basename($file),
                        'content' => null,
                        'error' => 'Invalid JSON string ' . json_last_error_msg()
                    ];
                } elseif (json_last_error() === JSON_ERROR_NONE) {
                    $data[] = [
                        'success' => true,
                        'filename' => basename($file),
                        'content' => $decoded,
                        'error' => null
                    ];
                }
            }
        } else {
            $data[] = [
                'success' => false,
                'filename' => null,
                'content' => null,
                'error' => 'Unable to find any files in "' . $this->directory . '"! Directory does not exist'
            ];
        }

        return $data;
    }

    public function deleteFile(string $filename): array
    {
        $path = $this->directory . '/' . basename($filename);

        if (!file_exists($path)) {
            return [
                'success' => false,
                'filename' => basename($filename),
                'error' => 'File does not exist'
            ];
        }

        $unlinked = unlink($path);
        if ($unlinked) {
            return [
                'success' => true,
                'filename' => basename($filename),
                'error' => null
            ];
        } else {
            return [
                'success' => false,
                'filename' => basename($filename),
                'error' => 'Could not delete file'
            ];
        }
    }

}

