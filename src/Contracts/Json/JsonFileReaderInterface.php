<?php

namespace JDS\Contracts\Json;

interface JsonFileReaderInterface
{
    public function read(string $path): array;
}

