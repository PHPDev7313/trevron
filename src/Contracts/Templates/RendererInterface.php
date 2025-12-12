<?php

namespace JDS\Contracts\Templates;

interface RendererInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function render(string $template, array $context = []): string;
}

