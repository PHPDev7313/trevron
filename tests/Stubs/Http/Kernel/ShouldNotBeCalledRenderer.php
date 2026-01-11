<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Contracts\Error\Rendering\ErrorRendererInterface;
use JDS\Error\ErrorContext;
use JDS\Http\Request;
use JDS\Http\Response;

class ShouldNotBeCalledRenderer implements ErrorRendererInterface
{
    public function render(Request $request, ErrorContext $context): Response
    {
        throw new \RuntimeException('ErrorResponder should not have been called');
    }
}

