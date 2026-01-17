<?php
declare(strict_types=1);

namespace JDS\Contracts\Http\Rendering;

use JDS\Http\Response;
use Throwable;

interface JsonErrorRendererInterface
{
    /**
     * Convert an exception into a JSON problem response.
     */
    public function render(Throwable $exception): Response;
}

