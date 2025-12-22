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

namespace JDS\Exceptions\Http;

use Exception;

class HttpException extends Exception
{
	private int $statusCode = 400;

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	public function setStatusCode(int $statusCode): void
	{
		$this->statusCode = $statusCode;
	}


}

