<?php

namespace JDS\Error\Response;
//
// Version 1.2 Final (v1.2 §13)
//

use JDS\Error\ErrorContext;
use JDS\Error\Rendering\ErrorRendererInterface;
use JDS\Error\Sanitization\ErrorSanitizer;
use JDS\Http\Request;
use JDS\Http\Response;

final class ErrorResponder
{
    public function __construct(
        private readonly ErrorSanitizer $sanitizer,
        private readonly ErrorRendererInterface $htmlRenderer,
        private readonly ErrorRendererInterface $jsonRenderer,
        private readonly ErrorRendererInterface $cliRenderer,
    ) {}

    public function respond(Request $request, ErrorContext $context): Response
    {
        $context = $this->sanitizer->sanitize($context);

        //
        // 1. CLI first (execution context should be deterministic)
        //
        if (method_exists($request, 'isCli') && $request->isCli()) {
            return $this->cliRenderer->render($request, $context);
        }

        //
        // If your Request has expectsJson(): use it.
        //
        if (method_exists($request, 'expectsJson') && $request->expectsJson()) {
            return $this->jsonRenderer->render($request, $context);
        }

        return $this->htmlRenderer->render($request, $context);
    }
}

