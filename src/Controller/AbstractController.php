<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

namespace JDS\Controller;

use JDS\Contracts\Rendering\RendererInterface;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Http\TemplateResponse;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{

    protected ContainerInterface $container;
    protected Request $request;

    /**
     * Called automatically by the RouterDispatcher before invoking a controller method.
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * The Request is injected by the RouterDispatcher or RequestHandler.
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Shortcut access to container services.
     */
    protected function get(string $id): mixed
    {
        return $this->container->get($id);
    }

    /**
     * Render a template using the application's RendererInterface.
     * Controllers should NOT contain rendering logic.
     */
    protected function render(string $template, array $params = []): Response
    {
        /** @var RendererInterface $renderer */
        $renderer = $this->container->get(RendererInterface::class);

        $content = $renderer->render($template, $params);

        return new Response($content, 200);
    }

    /**
     * Shortcut: return a JSON response.
     */
    protected function json(array $data, int $status = 200): Response
    {
        return new Response(json_encode($data), $status, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Redirect helper.
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return new Response('', $status, [
            'Location' => $url
        ]);
    }

    /**
     * Get a query parameter from the request.
     */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->request->getQueryParams($key, $default);
    }

    /**
     * Get a POST parameter from the request.
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $this->request->getPostParams($key, $default);
    }

    /**
     * Access the session (shortcut).
     */
    protected function session(): \JDS\Contracts\Session\SessionInterface
    {
        return $this->request->getSession();
    }

    protected function view(
        string $template,
        array $context = [],
        int $statusCode = 200
    ): TemplateResponse
    {
        return new TemplateResponse($template, $context, $statusCode);
    }
}

