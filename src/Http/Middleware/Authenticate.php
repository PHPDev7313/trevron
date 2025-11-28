<?php

namespace JDS\Http\Middleware;

use JDS\Contracts\Middleware\MiddlewareInterface;
use JDS\Contracts\Middleware\RequestHandlerInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Http\ForbiddenException;
use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;

class Authenticate implements MiddlewareInterface
{
	public function __construct(
		private SessionInterface $session
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
        $this->session->start();
		if (!$this->session->isAuthenticated()) {
			$this->session->setFlash('error', 'Please sign in first!');

			return new RedirectResponse($requestHandler->getContainer()->get('config')->get('routePath') . '/login');
		}
        // Retrieve the route metadata (roles, permissions, etc.)
        $routeMeta = $requestHandler->getRouteMeta();
        $requiredRoles = $routeMeta['roles'] ?? [];
        $requiredPermissions = $routeMeta['permissions'] ?? []; // Uses permission bitwise values
        // Retrieve user's session data
        $userRole = $this->session->get($this->session::AUTH_ROLE); // String: role_id
        $userPermissionBitwise = (int)$this->session->get($this->session::AUTH_ACCESS_LEVEL); // Integer for permission comparison
        // Role validation
        if (!empty($requiredRoles) && !$this->hasRequiredRole($userRole, $requiredRoles)) {
            throw new ForbiddenException('403 Forbidden: Insufficient role to access this route.');
        }
        // Permission validation
        if (!empty($requiredPermissions)
            && !$this->hasRequiredPermission($userPermissionBitwise, array_keys($requiredPermissions))) {
            throw new ForbiddenException('403 Forbidden: Insufficient permissions to access this route.');
        }
        // Proceed to the next middleware if role and permission checks pass
        return $requestHandler->handle($request);
    }

    /**
     * Validates if the user's role matches one of the required roles for the route.
     *
     * @param string|null $userRole
     * @param array $requiredRoles
     * @return bool
     */
    private function hasRequiredRole(?string $userRole, array $requiredRoles): bool
    {
        return in_array($userRole, $requiredRoles, true);
    }

    /**
     * Validates if the user's bitwise permission allows access to the route.
     *
     * @param int $userPermissionBitwise The user's permission bitwise value.
     * @param array $requiredPermissions Permission bitwise values defined in the route.
     * @return bool
     */
    private function hasRequiredPermission(int $userPermissionBitwise, array $requiredPermissions): bool
    {
        // The userPermissionBitwise must be <= each required permission bitwise value
        foreach ($requiredPermissions as $permissionBitwise) {
            if ($userPermissionBitwise > $permissionBitwise) {
                return false;
            }
        }
        return true;
    }
}

