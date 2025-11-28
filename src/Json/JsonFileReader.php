<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonFileReaderInterface;
use Throwable;

class JsonFileReader implements JsonFileReaderInterface
{

    public function read(string $path): array
    {
        try {
            if (!is_file($path)) {
                return ['success' => false, 'error' => "File not found: $path"];
            }

            $json = file_get_contents($path);

            return ['success' => true, 'data' => $json];

        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

