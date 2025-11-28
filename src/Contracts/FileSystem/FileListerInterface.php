<?php

namespace JDS\Contracts\FileSystem;

interface FileListerInterface
{
    public function list(string $directory, string $extension = 'json'): array;
}

