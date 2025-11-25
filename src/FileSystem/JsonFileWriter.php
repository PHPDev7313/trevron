<?php

namespace JDS\FileSystem;

use JDS\Contracts\FileSystem\JsonFileWriterInterface;
use Throwable;

class JsonFileWriter implements JsonFileWriterInterface
{
    public function write(string $path, string $json): array
    {
        try {
            file_put_contents($path, $json);
            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

