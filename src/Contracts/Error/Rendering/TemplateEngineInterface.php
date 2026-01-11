<?php

namespace JDS\Contracts\Error\Rendering;

interface TemplateEngineInterface
{
    /** @param array<string,mixed> $context */
    public function render(string $template, array $context): string;
}

