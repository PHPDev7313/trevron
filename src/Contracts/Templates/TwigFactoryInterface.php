<?php

namespace JDS\Contracts\Templates;

use Twig\Environment;

interface TwigFactoryInterface
{
    public function create(): Environment;

}

