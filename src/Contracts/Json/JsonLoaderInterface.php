<?php

namespace JDS\Contracts\Json;

interface JsonLoaderInterface
{
    public function loadAll(string $directory): array;
}

