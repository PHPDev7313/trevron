<?php

namespace JDS\Contracts\FileSystem;

interface FileReaderInterface
{
    public function read(string $path): string;
}

