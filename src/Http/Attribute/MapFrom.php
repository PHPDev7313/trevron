<?php

namespace JDS\Http\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class MapFrom
{
    public function __construct(
        public readonly string $param
    )
    {
    }
}

