<?php

namespace JDS\Http\Navigation;

class NavigationMetadataCollection
{
    private array $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function all(): array
    {
        return $this->items;
    }
}

