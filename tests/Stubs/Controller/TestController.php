<?php

namespace Tests\Stubs\Controller;

use JDS\Controller\AbstractController;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Http\TemplateResponse;

class TestController extends AbstractController
{
    public function simple(): Response
    {
        return new Response("ok", 200);
    }

    public function withParams(string $id): Response
    {
        return new Response("id:{$id}", 200);
    }

    public function withUserId(string $userId): Response
    {
        return new Response("userId:{$userId}", 200);
    }

    public function withRequest(Request $request): Response
    {
        return new Response("method:{$request->getMethod()}", 200);
    }

    public function template(): TemplateResponse
    {
        return new TemplateResponse("hello.twig", ["x" => 1], 200);
    }

    public function invalid(): string
    {
        return "not a response";
    }
}

