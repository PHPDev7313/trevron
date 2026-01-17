<?php

namespace JDS\Contracts\Http\Response;

use JDS\Http\Response;

interface HttpRendererInterface
{
    /**
     * Render a view into an HTTP response.
     *
     * @param array<string,mixed> $context
     */
    public function render(
        string $template,
        array $context = [],
        int $status = 200,
        array $headers = []
    ): Response;
}

