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
    public function getFilesData(): array|bool
    {
        $data = [];

        if (!is_dir($this->directory)) {
            return false;
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
            return false;
        }
        // only return those where 'success' = true
        return $this->filterSuccess($data);
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

    private function filterSuccess(array $data): array
    {
        $filteredData = array_filter($data, function($item) {
            return isset($item['success']) && $item['success'] === true;
        });
        return array_values($filteredData);
    }

}

