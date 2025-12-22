<?php

namespace JDS\Error\Rendering;
//
// Version 1.2 Final (v1.2 §8)
//

use JDS\Error\ErrorContext;
use JDS\Error\Rendering\ErrorRendererInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class JsonErrorRenderer implements ErrorRendererInterface
{

    public function render(Request $request, ErrorContext $context): Response
    {
        $payload = [
            'error' => [
                'status'    => $context->httpStatus,
                'code'      => [
                        'key'   => $context->statusCode->key(),
                        'value' => $context->statusCode->valueInt(),
                ],
                'category'  => [
                        'key'   => $context->category->key(),
                        'value' => $context->category->valueInt(),
                ],
                'message'   => $context->publicMessage,
            ],
        ];

        if ($context->hasDebug()) {
            $payload['error']['debug'] = $context->debug;
        }

        return new Response(
            json_encode($payload, JSON_THROW_ON_ERROR),
            $context->httpStatus,
            ['Content-Type' => 'application/json']
        );
    }
}

