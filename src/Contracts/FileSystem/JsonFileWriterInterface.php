<?php

namespace JDS\Contracts\FileSystem;

interface JsonFileWriterInterface
{
    public function write(string $path, string $json): array;
}

