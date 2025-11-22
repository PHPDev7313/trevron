<?php

namespace JDS\FileSystem;

interface FileDeleterInterface
{
    public function delete(string $path): array;
}

