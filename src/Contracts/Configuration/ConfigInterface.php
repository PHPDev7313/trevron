<?php

namespace JDS\Contracts\Configuration;

interface ConfigInterface
{
    /**
     * Retrieves the value associated with the specified key.
     *
     * @param string $key The key to search for within the collection.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value associated with the specified key, or the default value if the key does not exist.
     */
    public function get(string $key, mixed $default=null): mixed;

    /**
     * Checks if the specified key exists in the collection.
     *
     * @param string $key The key to check for existence.
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key): bool;

    /**
     * Retrieves all items from the collection or dataset.
     *
     * @return array<string, mixed> An array containing all the items.
     */
    public function all(): array;

}

