<?php

namespace JDS\Contracts\FileSystem;

interface DirectoryScannerInterface
{
    public function getFiles(): array;

    public function getDirectory(): string;

}

