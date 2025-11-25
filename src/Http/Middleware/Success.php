<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class Success implements MiddlewareInterface
{

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		return new Response('It was a success! it worked!!', 200);
	}
}

