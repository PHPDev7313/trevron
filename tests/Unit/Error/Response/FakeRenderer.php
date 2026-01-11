<?php

namespace Tests\Unit\Error\Response;

use JDS\Contracts\Error\Rendering\ErrorRendererInterface;
use JDS\Http\Response;

final class FakeRenderer implements ErrorRendererInterface
{
    public bool $called = false;

    public function render($request, $context): Response
    {
        $this->called = true;
        return new Response('ok', 500);
    }
}

