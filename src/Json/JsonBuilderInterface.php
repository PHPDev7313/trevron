<?php

namespace JDS\Json;
interface JsonBuilderInterface
{
    public function save(mixed $data, string $directory, string $baseName): array;

}

