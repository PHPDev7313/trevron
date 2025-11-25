<?php

namespace JDS\Contracts\Json;
interface JsonBuilderInterface
{
    public function save(mixed $data, string $directory, string $baseName): array;

}

