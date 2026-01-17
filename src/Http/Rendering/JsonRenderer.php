<?php
declare(strict_types=1);

namespace JDS\Http\Rendering;

use JDS\Contracts\Http\Rendering\JsonRendererInterface;
use JDS\Error\StatusCode;
use JDS\Exceptions\Http\HttpRuntimeException;
use JDS\Http\Response;
use Throwable;

final class JsonRenderer implements JsonRendererInterface
{

    /**
     * @inheritDoc
     */
    public function render(
        array $data,
        int $status = 200,
        array $headers = []
    ): Response
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new HttpRuntimeException(
                StatusCode::JSON_ENCODING_FAILED->name,
                StatusCode::JSON_ENCODING_FAILED->value,
                previous: $e
            );
        }

        return new Response(
            content: $json,
            status: $status,
            headers: $headers + [
                'Content-Type' => 'application/json',
            ]
        );
    }
}

