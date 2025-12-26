<?php

namespace JDS\Contracts\Console;

interface CommandRegistryInterface
{
    public function register(string $commandClass): void;

    /**
     * @return class-string[]
     */
    public function all(): array;
}

