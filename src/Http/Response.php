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
declare(strict_types=1);

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
	}

	public function send(): void
	{
        // must be set before sending content
        // so best to create on instantiation like here
        http_response_code($this->status);

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
		return $this->headers[$header] ?? null;
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

    public function getHeaderLine(string $name): string
    {
        $value = $this->headers[$name] ?? null;
        if ($value === null) {
            return '';
        }
        return is_array($value) ? implode(', ', $value) : (string) $value;
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function withStatus(int $status): self
    {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }
}


