<?php

namespace JDS\Rendering;

use JDS\Contracts\Rendering\RendererInterface;
use Twig\Environment;

class TwigRenderer implements RendererInterface
{
    public function __construct(
        private readonly Environment $twig
    ) {}

    public function render(string $template, array $params = []): string
    {
        return $this->twig->render($template, $params);
    }
}