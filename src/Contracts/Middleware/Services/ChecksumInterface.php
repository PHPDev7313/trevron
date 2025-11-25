<?php

namespace JDS\Contracts\Middleware\Services;

interface ChecksumInterface
{
    public function generate(object $entity): string;

    public function normalize(object $entity): array;
}

