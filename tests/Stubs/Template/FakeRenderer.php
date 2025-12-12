<?php

namespace Tests\Stubs\Template;

use JDS\Contracts\Rendering\RendererInterface;

final class FakeRenderer implements RendererInterface
{

    public array $last = [];

    public function render(string $template, array $params = []): string
    {
        $this->last = ['template' => $template, 'params' => $params];
        return "rendered:{$template}";
    }

//    private int $renderCount = 0;
//
//    private ?string $lastTemplate = null;
//
//    /** @var array<string, mixed>|null */
//    private ?array $lastParams = null;
//
//    /**
//     * @inheritDoc
//     */
//    public function render(string $template, array $params = []): string
//    {
//        $this->renderCount++;
//        $this->lastTemplate = $template;
//        $this->lastParams = $params;
//
//        //
//        // Deterministic, test-friendly content
//        //
//        return 'rendered:' . $template . ':' . json_encode($params, JSON_THROW_ON_ERROR);
//    }
//
//    public function getRenderCount(): int
//    {
//        return $this->renderCount;
//    }
//
//    public function getLastTemplate(): ?string
//    {
//        return $this->lastTemplate;
//    }
//
//    public function getLastParams(): ?array
//    {
//        return $this->lastParams;
//    }
//
//    public function reset(): void
//    {
//        $this->renderCount = 0;
//        $this->lastTemplate = null;
//        $this->lastParams = null;
//    }
}

