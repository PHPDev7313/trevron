<?php

namespace JDS\Dbal\Migration;

final class MigrationNameNormalizer
{
    public function normalize(string $input): string
    {
        // remove leading "m"
        $input = ltrim($input, 'm');

        // strip suffixes
        $input = explode('_', $input)[0];
        $input = explode('.', $input)[0];

        return ltrim($input, '0') ?: '0';
    }

    public function toInt(string $input): int
    {
        return (int) $this->normalize($input);
    }
}

