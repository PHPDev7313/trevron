<?php
declare(strict_types=1);

namespace JDS\Http\Rendering;

use JDS\Contracts\Http\Rendering\JsonErrorRendererInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;
use JDS\Http\Response;
use Throwable;

final class JsonErrorRenderer implements JsonErrorRendererInterface
{

    public function __construct(
        private readonly bool $debug = false
    ) {}

    /**
     * @inheritDoc
     */
    public function render(Throwable $exception): Response
    {
        // --------------------------------------
        // Normalize exception to StatusCode
        // --------------------------------------
        if ($exception instanceof StatusException) {
            $statusCode = $exception->getStatusCodeEnum();
            $httpStatus = $exception->getHttpStatus();
        } else {
            $statusCode = StatusCode::HTTP_KERNEL_GENERAL_FAILURE;
            $httpStatus = 500;
        }

        $payload = [
            'error' => [
                'code'      => $statusCode->valueInt(),
                'key'       => $statusCode->name,
                'category'  => $statusCode->categoryKey(),
                'message'   => $statusCode->defaultMessage(),
            ],
        ];

        if ($this->debug) {
            $payload['debug'] = [
                'exception' => $exception::class,
                'message'   => $exception->getMessage(),
            ];
        }

        // --------------------------------------------
        // Rendering must NEVER throw
        // --------------------------------------------
        try {
            $json = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $json = '{"error":{"code":500,"key":"INTERNAL_ERROR","message":"Fatal error"}}';
            $httpStatus = 500;
        }

        return new Response(
            content: $json,
            status: $httpStatus,
            headers: [
                'Content-Type' => 'application/problem+json',
            ]
        );
    }
}

