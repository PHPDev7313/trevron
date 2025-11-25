<?php

namespace JDS\Contracts\FileSystem;

interface FileDeleterInterface
{
    public function delete(string $path): array;
}

