<?php

namespace JDS\Contracts\Rendering;

interface RendererInterface
{
    /**
     * Render a template to a string.
     *
     * @param string $template  Template name or path
     * @param array  $params    Variables passed to the template
     */
    public function render(string $template, array $params = []): string;
}

