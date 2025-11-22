<?php

namespace JDS\FileSystem;

interface FileReaderInterface
{
    public function read(string $path): string;
}

