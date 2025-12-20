<?php

namespace JDS\Contracts\Http\Navigation;

interface MenuGeneratorInterface
{
    // make a constructor __construct(array $routes);

    public function generateMenu(): array;
}

