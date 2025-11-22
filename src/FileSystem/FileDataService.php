<?php

namespace JDS\FileSystem;

use JDS\Parsing\JsonDecoder;
use Throwable;

class FileDataService implements FileDataServiceInterface
{
    public function __construct(
        private DirectoryScanner $scanner,
        private FileReader $reader,
        private JsonDecoder $decoder
    )
    {
    }

    public function getFilesData(): array
    {
        $results = [];

        foreach ($this->scanner->getFiles() as $file) {
            $path = $this->scanner->getDirectory() . '/' . $file;

            try {
                $raw = $this->reader->read($path);
                $decoded = $this->decoder->decode($raw);

                $results[] = [
                    'success' => true,
                    'filename' => $file,
                    'content' => $decoded,
                    'error' => null
                ];
            } catch (Throwable $e) {
                // log here
                $results[] = [
                    'success' => false,
                    'filename' => $file,
                    'content' => null,
                    'error' => $e->getMessage()
                ];
            }
        }
        return array_filter($results, fn($r) => $r['success'] === true);
    }
}

