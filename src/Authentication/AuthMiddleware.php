<?php

namespace JDS\Authentication;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Http\Request;
use JDS\Http\Response;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionInterface $session,
        private ?array $required = null // e.g. ['permission' => 'products.edit']
    )
    {
    }

    public function process(Request $request, RequestHandlerInterface $next): Response
    {
        // 1. Must be logged in
        $user_id = $this->session->get($this->session::AUTH_KEY);
        if (!$user_id) {
            return $this->deny('Not Authentivated');
        }

        // 2. Admin always allowed
        $isAdmin = (bool)$this->session->get($this->session::AUTH_ADMIN);
        if ($isAdmin) {
            return $next->handle($request);
        }

        // 3. If no requirements, just proceed
        if ($this->required === null) {
            return $next->handle($request);
        }

        // 4. Check permissions
        if (isset($this->required['permission'])) {
            $perm = $this->required['permission'];
            $userPerm = $this->session->get($this->session::AUTH_PERMISSION);

            // single permissions match
            if ($userPerm !== $perm) {
                return $this->deny('Insufficient permissions');
            }
        }

        return $next->handle($request);
    }

    private function deny(string $message): Response
    {
        return new Response(
            json_encode(['error' => $message]),
            403,
            ['Content-Type' => 'application/json'],
        );
    }
}

