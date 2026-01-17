<?php
declare(strict_types=1);

namespace JDS\Contracts\Http\Rendering;

use JDS\Http\Response;

interface HttpRendererInterface
{
    /**
     * Render a view into an HTTP response.
     *
     * @param array<string,mixed> $context
     * @param array<string,string> $headers
     */
    public function render(
        string $template,
        array $context = [],
        int $status = 200,
        array $headers = []
    ): Response;
}

