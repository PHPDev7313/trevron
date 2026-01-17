<?php
declare(strict_types=1);

namespace JDS\Contracts\Http\Rendering;

use JDS\Http\Response;

interface JsonRendererInterface
{
    /**
     * Render data into a JSON HTTP response.
     *
     * @param array<string,mixed> $data
     * @param array<string,string> $headers
     */
    public function render(
        array $data,
        int $status = 200,
        array $headers = []
    ): Response;
}

