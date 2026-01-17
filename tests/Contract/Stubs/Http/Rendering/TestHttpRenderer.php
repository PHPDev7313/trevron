<?php

namespace Tests\Contract\Stubs\Http\Rendering;

use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Contracts\Http\Rendering\HttpRendererInterface;
use JDS\Http\Response;

class TestHttpRenderer implements HttpRendererInterface
{
    public function __construct(
        private readonly TemplateEngineInterface $engine
    ) {}

    /**
     * @inheritDoc
     */
    public function render(
        string $template,
        array $context = [],
        int $status = 200,
        array $headers = []
    ): Response
    {
        return new Response(
            content: $this->engine->render($template, $context),
            status: $status,
            headers: $headers + [
                'Content-Type' => 'text/html; charset=utf-8',
            ]
        );
    }
}

