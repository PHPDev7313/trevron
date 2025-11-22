<?php

namespace JDS\FileSystem;

interface FilePathValidatorInterface
{
    public function validate(string $path): array;
}