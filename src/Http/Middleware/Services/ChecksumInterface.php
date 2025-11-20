<?php

namespace JDS\Http\Middleware\Services;

interface ChecksumInterface
{
    public function generate(object $entity): string;

    public function normalize(object $entity): array;
}

