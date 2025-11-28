<?php

namespace JDS\Json;

use JDS\Contracts\Json\JsonSorterInterface;

class JsonSorter implements JsonSorterInterface
{

    public function sortByOldest(array $files): array
    {
        usort($files, fn($a, $b) => filemtime($a) <=> filemtime($b));

        return $files;
    }

    public function sortNewest(array $files): array
    {
        usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

        return $files;
    }

    public function sortByName(array $files): array
    {
        sort($files);
        return $files;
    }
}

