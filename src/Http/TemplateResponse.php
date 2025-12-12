<?php

namespace JDS\Http;

use JDS\Contracts\Rendering\RendererInterface;

final class TemplateResponse
{
    private string $template;
    /** @var array<string, mixed> */
    private array $context;
    private int $statusCode;
    /** @var array<string, string> */
    private array $headers;

    private ?string $buffer = null;

    /**
     * @param array<string, mixed> $context
     * @param array<string, string> $headers
     */
    public function __construct(
        string $template,
        array $context = [],
        int $statusCode = 200,
        array $headers = []
    )
    {
        $this->template     = $template;
        $this->context      = $context;
        $this->statusCode   = $statusCode;
        $this->headers      = $headers;

        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = 'text/html; charset=utf-8';
        }
    }

    /**
     * On clone, invalidate any existing render buffer.
     */
    public function __clone()
    {
        $this->buffer = null;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    //
    // ========== Immutability helpers ==========
    //
    public function withTemplate(string $template): self
    {
        $clone = clone $this;
        $clone->template = $template;

        return $clone;
    }

    public function withContext(array $context): self
    {
        $clone = clone $this;
        $clone->context = $context;

        return $clone;
    }

    /**
     * Merge additional context into existing.
     *
     * @param array<string, mixed> $context
     */
    public function withAddedContext(array $context): self
    {
        $clone = clone $this;
        $clone->context = array_merge($clone->context, $context);

        return $clone;
    }

    public function withStatusCode(int $statusCode): self
    {
        $clone = clone $this;
        $clone->statusCode = $statusCode;

        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;

        return $clone;
    }

    //
    // ========== Rendering & buffering ==========
    //
    public function render(RendererInterface $renderer): string
    {
        if ($this->buffer === null) {
            $this->buffer = $renderer->render($this->template, $this->context);
        }

        return $this->buffer;
    }

    public function toResponse(RendererInterface $renderer): Response
    {
        $body = $this->render($renderer);

        //
        // If your Response has a different signature, tweak this accordingly.
        return new Response(
            $body,
            $this->statusCode,
            $this->headers
        );
    }

}


