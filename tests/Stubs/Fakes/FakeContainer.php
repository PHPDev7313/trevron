<?php

namespace Tests\Stubs\Fakes;

use Psr\Container\ContainerInterface;

class FakeContainer implements ContainerInterface
{

    public function __construct(private array $entries = [])
    {}
    public function get(string $id)
    {
        return $this->entries[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->entries[$id]);
    }
}

