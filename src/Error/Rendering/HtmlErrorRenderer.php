<?php
//
// Version 1.2 Final (v1.2 §8)
//

namespace JDS\Error\Rendering;

use JDS\Contracts\Rendering\RendererInterface;
use JDS\Error\ErrorContext;

use JDS\Http\Request;
use JDS\Http\Response;

final class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly RendererInterface $twig
    ) {}

    public function render(Request $request, ErrorContext $context): Response
    {
        $html = $this->twig->render('errors/error.html.twig', [
            'status'    => $context->httpStatus,
            'message'   => $context->publicMessage,
            //
            // Explicit, enum-safe values
            //
            'code'      => $context->statusCode->key(),
            'category'  => $context->statusCode->categoryKey(),

            //
            // Sanitizer gurantees this is safe
            // will be empty in production automatically
            //
            'debug'     => $context->debug,
        ]);

        return new Response(
            content: $html,
            status: $context->httpStatus
        );
    }
}

