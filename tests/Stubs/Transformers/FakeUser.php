<?php

namespace Tests\Stubs\Transformers;

final class FakeUser
{
    public function __construct(
        public string|int $id,
        public string $name
    )
    {
    }
}

