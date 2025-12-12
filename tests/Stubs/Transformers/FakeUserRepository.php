<?php

namespace Tests\Stubs\Transformers;

final class FakeUserRepository
{
    /** @var array<string,FakeUser>  */
    private array $users= [];

    /**
     * @param array<string,FakeUser> $seed
     */
    public function __construct(array $seed = [])
    {
        $this->users = $seed;
    }

    /**
     * @param string|int $id
     */
    public function findById(string|int $id): ?FakeUser
    {
        $normalized = $this->normalize($id);
        return $this->users[$normalized] ?? null;
    }

    private function normalize(string|int $id): string
    {
        return (string)$id;
    }
}

