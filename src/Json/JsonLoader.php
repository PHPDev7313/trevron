<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonLoaderInterface;
use JDS\FileSystem\FileLister;
use JDS\FileSystem\FilePathValidator;

class JsonLoader implements JsonLoaderInterface
{

    public function __construct(
        private FileLister $lister,
        private JsonSorter $sorter,
        private JsonFileReader $reader,
        private JsonDecoder $decoder,
        private FilePathValidator $validator
    )
    {
    }

    public function loadAll(string $directory, bool $assoc=false): array
    {
        // Validate directory
        $dirCheck = $this->validator->validate($directory);
        if (!$dirCheck['success']) {
            return $dirCheck;
        }

        // list files
        $list = $this->lister->list($directory);
        if (!$list['success']) {
            return $list;
        }

        // sort
        $files = $this->sorter->sortByOldest($list['files']);

        $results = [];

        foreach ($files as $file) {

            $read = $this->reader->read($file);
            if (!$read['success']) {
                continue; // ignore or collect errors
            }

            $decode = $this->decoder->decode($read['data'], $assoc);
            if ($decode['success']) {
                $results[] = $decode['data'];
            }
        }

        return [
            'success' => true,
            'data' => $results
        ];
    }
}

