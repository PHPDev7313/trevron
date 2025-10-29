<?php

namespace JDS\Http\Generators;

interface FileNameGeneratorInterface
{
    public function generate(?string $baseName = 'file', string $extension = 'json', ?string $directory=null, bool $useTimestamp=true): string;

    public function generateUnique(string $directory, ?string $baseName = 'file', string $extension = 'json'): string;

}

