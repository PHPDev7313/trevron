<?php

namespace JDS\FileSystem;

interface JsonFileWriterInterface
{
    public function write(string $path, string $json): array;
}

