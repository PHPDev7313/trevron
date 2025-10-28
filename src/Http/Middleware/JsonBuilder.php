<?php

namespace JDS\Http\Middleware;

use JDS\Http\Middleware\MiddlewareInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class JsonBuilder implements MiddlewareInterface
{

    public function process(Request $request, RequestHandlerInterface $requestHandler): Response
    {
        // Execute the next middleware or controller
        $response = $requestHandler->handle($request);

        // Retrieve the content
        $content = $response->getContent();

        // Only act if content is array-like
        if (!is_array($content)) {
            return $response;
        }

        // Try to encode the array to JSON
        [$json, $error] = $this->encodeJson($content);

        if ($error !== null) {
            return $this->buildErrorResponse($error);
        }

        // Mutate the response safely
        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $response->setContent($json);

        return $response;
    }


    /**
     * validates and safely encodes array data to JSON.
     */
    private function encodeJson(array $data): array
    {
        // Detect unsupported values
        foreach ($data as $key => $value) {
            if (is_resource($value)) {
                return [null, "Invalid value for key '{$key}': resource type not supported"];
            }
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [null, "JSON encoding failed: " . json_last_error_msg()];
        }

        return [$json, null];
    }


    /**
     * Builds a proper JSON error response.
     */
    private function buildErrorResponse(string $message): Response
    {
        $response = new Response();
        $response->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
        $response->setContent(json_encode([
            'status' => 'error',
            'message' => $message
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

