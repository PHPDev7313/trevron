<?php

namespace JDS\Contracts\Http\Generators;

interface MenuGeneratorInterface
{
    // make a constructor __construct(array $routes);

    public function generateMenu(): array;
}

