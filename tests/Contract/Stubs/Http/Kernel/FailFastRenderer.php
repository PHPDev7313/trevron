<?php

namespace Tests\Contract\Stubs\Http\Kernel;

use JDS\Contracts\Error\Rendering\ErrorRendererInterface;
use JDS\Http\Response;
use RuntimeException;

final class FailFastRenderer implements ErrorRendererInterface
{

    public function render($request, $context): Response
    {
        throw new RuntimeException('ErrorResponder invoked unexpectedly in success path');
    }
}