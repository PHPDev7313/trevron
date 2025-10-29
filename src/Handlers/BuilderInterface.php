<?php

namespace JDS\Handlers;

interface BuilderInterface
{
    public function build(mixed $data): array;

    public function saveToFile(mixed $data): array;

}

