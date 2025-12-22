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

namespace JDS\Http;

class Response
{
	public const HTTP_INTERNAL_SERVER_ERROR = 500;
	public const HTTP_FORBIDDEN = 403;
	public const HTTP_NOT_FOUND = 404;
	public const HTTP_METHOD_NOT_ALLOWED = 405;


	public function __construct(
		private ?string $content = '',
		private int $status = 200,
		private array $headers = []
	)
	{
		// must be set before sending content
		// so best to create on instantiation like here
		http_response_code($this->status);
	}

	public function send(): void
	{
		// start output buffering
		ob_start();

		// send headers
		foreach ($this->headers as $name => $value) {
			header("$name: $value");
		}

		// this will actually add the content to the buffer
		echo $this->content;

		// flush the buffer, sending the content to the client
		ob_end_flush();
	}

	public function setContent(?string $content): void
	{
		$this->content = $content;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function getHeader(string $header): mixed
	{
		return $this->headers[$header];
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function setHeader($key, $value): void
	{
		$this->headers[$key] = $value;
	}

	public function getContent(): ?string
	{
		return $this->content;
	}


    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function setStatusCode(int $status): void
    {
        $this->status = $status;
    }
}


