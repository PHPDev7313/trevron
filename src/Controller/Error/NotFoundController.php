<?php

namespace JDS\Controller\Error;

use JDS\Http\Response;

class NotFoundController
{
    public function __invoke(): Response
    {
        return new Response(
            content: '404 — Page Not Found',
            status: 404
        );
    }
}

