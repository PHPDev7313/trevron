<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;

class OldAuthenticate implements MiddlewareInterface
{
	public function __construct(
		private SessionInterface $session
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $next): Response
	{
        $this->session->start();

		if (!$this->session->isAuthenticated()) {
			$this->session->setFlash('error', 'Please sign in first!');

			return new RedirectResponse($next->getContainer()->get('routePath') . '/login');
		}

		return $next->handle($request);
	}
}
