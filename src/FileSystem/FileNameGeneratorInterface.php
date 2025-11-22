<?php

namespace JDS\FileSystem;

interface FileNameGeneratorInterface
{
    public function generate(string $baseName, string $extension = 'json'): string;

    public function makeUnique(string $directory, string $fileName): string;

}

