<?php

namespace JDS\Contracts\Json;

interface JsonSorterInterface
{
    public function sortByOldest(array $files): array;

    public function sortNewest(array $files): array;

    public function sortByName(array $files): array;
}

