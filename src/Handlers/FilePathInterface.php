<?php

namespace JDS\Handlers;

interface FilePathInterface
{
    public function validatePath(string $filePath): array;

    public function validateDirectory(string $directory): array;
}