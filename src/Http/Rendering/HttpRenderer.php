<?php
declare(strict_types=1);

namespace JDS\Http\Rendering;

use JDS\Contracts\Error\Rendering\TemplateEngineInterface;
use JDS\Contracts\Http\Rendering\HttpRendererInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Http\HttpRuntimeException;
use JDS\Http\Response;
use Throwable;

final class HttpRenderer implements HttpRendererInterface
{
    public function __construct(
        private readonly TemplateEngineInterface $engine,
    )
    {
    }

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
        try {
            $content = $this->engine->render($template, $context);
        } catch (Throwable $e) {
            throw new HttpRuntimeException(
                StatusCode::TEMPLATE_RENDERING_FAILED->name,
                StatusCode::TEMPLATE_RENDERING_FAILED->value,
                previous: $e
            );
        }
        return new Response(
            content: $content,
            status: $status,
            headers: $headers + [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
        );
    }
}

