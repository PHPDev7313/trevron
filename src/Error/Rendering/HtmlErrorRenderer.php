<?php
//
// Version 1.2 Final (v1.2 §8)
// Locked
//

namespace JDS\Error\Rendering;

use JDS\Contracts\Error\Rendering\ErrorRendererInterface;
use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Error\ErrorContext;
use JDS\Http\Request;
use JDS\Http\Response;

/**
 * HtmlErrorRenderer v1.2 FINAL
 *
 * This file is ARCHITECTURALLY FROZEN.
 *
 * Any behavioral change requires:
 * - Contract test updates
 * - Version bump (v1.3+)
 * - Architecture review
 * -
 */
final class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly TemplateEngineInterface $templates
    ) {}

    public function render(Request $request, ErrorContext $context): Response
    {
        $template = match ($context->httpStatus) {
            404 => "errors/404.html.twig",
            default => 'errors/500.html.twig',
        };

        $view = [
            'status' => $context->httpStatus,
            'code' => [
                'key' => $context->statusCode->key(),
                'value' => $context->statusCode->valueInt(),
            ],
            'category' => [
                'key' => $context->category->key(),
                'value' => $context->category->valueInt(),
            ],
            'message' => $context->publicMessage,
        ];

        if ($context->hasDebug()) {
            $view['debug'] = $context->debug;
        }

        return new Response(
            $this->templates->render($template, $view),
            $context->httpStatus,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }
}

//        $html = $this->twig->render('errors/error.html.twig', [
//            'status'    => $context->httpStatus,
//            'message'   => $context->publicMessage,
//            //
//            // Explicit, enum-safe values
//            //
//            'code'      => $context->statusCode->key(),
//            'category'  => $context->statusCode->categoryKey(),
//
//            //
//            // Sanitizer gurantees this is safe
//            // will be empty in production automatically
//            //
//            'debug'     => $context->debug,
//        ]);
//
//        return new Response(
//            content: $html,
//            status: $context->httpStatus
//        );
