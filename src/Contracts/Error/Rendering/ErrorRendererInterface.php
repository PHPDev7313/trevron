<?php

namespace JDS\Contracts\Error\Rendering;
//
// Version 1.2 Final (v1.2 §8)
//

use JDS\Error\ErrorContext;
use JDS\Http\Request;
use JDS\Http\Response;

interface ErrorRendererInterface
{
    public function render(Request $request, ErrorContext $context): Response;
}

