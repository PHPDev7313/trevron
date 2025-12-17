<?php

namespace JDS\Error\Rendering;
//
// Version 1.2 Final (v1.2 §8)
//

use JDS\Error\ErrorContext;
use JDS\Error\Rendering\ErrorRendererInterface;
use JDS\Http\Request;
use JDS\Http\Response;

final class CliErrorRenderer implements ErrorRendererInterface
{

    public function render(Request $request, ErrorContext $context): Response
    {
        $lines = [];
        $lines[] = "Error {$context->httpStatus} {$context->publicMessage}";
        $lines[] = "Code: " . ($context->statusCode ?? (string)$context->statusCode);
        $lines[] = "Category: " . ($context->category ?? (string)$context->category);

        if ($context->exception) {
            $lines[] = "Exception: " . get_class($context->exception) . ': ' . $context->exception->getMessage();
            $lines[] = $context->exception->getTraceAsString();
        }

        if ($context->hasDebug()) {
            $lines[] = "Debug: " . json_encode($context->debug, JSON_THROW_ON_ERROR);
        }

        return new Response(implode(PHP_EOL, $lines) . PHP_EOL, $context->httpStatus, ['Content-Type' => 'text/plain']);
    }
}

