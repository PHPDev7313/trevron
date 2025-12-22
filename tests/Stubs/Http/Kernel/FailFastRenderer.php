<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Error\ErrorContext;
use JDS\Error\Rendering\ErrorRendererInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class FailFastRenderer implements ErrorRendererInterface
{
    public function render(Request $request, ErrorContext $context): Response
    {
        throw new \RuntimeException(
            'ErrorResponder should NOT have been invoked in this test'
        );
    }
}

