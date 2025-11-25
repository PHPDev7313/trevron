<?php

namespace JDS\Contracts\FileSystem;

interface FilePathValidatorInterface
{
    public function validate(string $path): array;
}