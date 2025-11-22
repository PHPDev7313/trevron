<?php

namespace JDS\FileSystem;

interface DirectoryScannerInterface
{
    public function getFiles(): array;

    public function getDirectory(): string;

}

