<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Guest implements MiddlewareInterface
{
	public function __construct(private SessionInterface $session)
	{
	}

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 */
	public function process(Request $request, RequestHandlerInterface $next): Response
	{
		$this->session->start();

		if ($this->session->isAuthenticated()) {

			return new RedirectResponse($next->getContainer()->get('config')->get('routePath') . '/');
		}

		return $next->handle($request);
	}
}


