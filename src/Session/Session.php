<?php

namespace JDS\Session;

use JDS\Contracts\Session\SessionInterface;
use JDS\Http\CannotBeNullException;
use Random\RandomException;

class Session implements SessionInterface
{
    private const FLASH_KEY = 'flash';
    /**
     * authenticated User ID
     *
     * string(12): User ID
     */
    public const AUTH_KEY = 'auth_id'; // string users.user_id
    /**
     * integer(2): Bitwise (Permission)
     */
    public const AUTH_BITWISE = 'auth_bitwise'; // integer from permissions.bitwise
    /**
     * string(12): Permission ID
     */
    public const AUTH_PERMISSION = 'auth_permission_id'; // string from permissions.permission_id
    /**
     * string(12): Role ID
     */
    public const AUTH_ROLE = 'auth_role_id'; // string from roles.role_id
    /**
     * integer(2): Role Weight (Role)
     */
    public const AUTH_ROLE_WEIGHT = 'auth_role_weight'; // integer from roles.role_weight
    public const CSRF_TOKEN = 'csrf_token';
    public const ACCESS_TOKEN = 'access_token';
    public const REFRESH_TOKEN = 'refresh_token';
    public const AUTH_ADMIN = 'auth_admin'; // bool
    public const ADMINISTRATOR = 'admin';

    public function __construct(string $prefix=null)
    {
        if (is_null($prefix)) {
            throw new CannotBeNullException('Session prefix not defined');
        }
        if (!defined("PREFIX")) {
            define("PREFIX", $prefix);
        }
    }

    /**
     * @throws RandomException
     */
    public function start(): void
    {

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_start();

        if (!$this->has(self::CSRF_TOKEN)) {
            $this->set(self::CSRF_TOKEN, bin2hex(random_bytes(32)));
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[PREFIX][$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        if (strtolower($key) === 'all') {
            return $_SESSION[PREFIX] ?? $default;
        }
        return $_SESSION[PREFIX][$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[PREFIX][$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[PREFIX][$key]);
    }

    public function getFlash(string $key): array
    {
        $flash = $this->get(self::FLASH_KEY) ?? [];
        if (isset($flash[$key])) {
            $messages = $flash[$key];
            unset($flash[$key]);
            $this->set(self::FLASH_KEY, $flash);
            return $messages;
        }
        return [];
    }

    public function setFlash(string $type, string $message): void
    {
        $type = strtolower($type);
        $flash = $this->get(self::FLASH_KEY) ?? [];
        $flash[$type][] = $message;
        $this->set(self::FLASH_KEY, $flash);
    }

    public function hasFlash(string $type): bool
    {
        return isset($_SESSION[PREFIX][self::FLASH_KEY][$type]);
    }

    public function clearFlash(): void
    {
        unset($_SESSION[PREFIX][self::FLASH_KEY]);
    }

    public function isAuthenticated(): bool
    {
        return $this->has(self::AUTH_KEY);
    }

    public function isNotAuthenticated(): bool
    {
        return !$this->isAuthenticated();
    }

    public function destroy(): void
    {
        // Clear all session data
        $this->clear();

        // Unset the session cookie, if it exists
        $this->invalidateSessionCookie();
        // terminate session on the server
        session_destroy();
    }

    private function invalidateSessionCookie(): void
    {
        // Check if cookies are being used
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                1,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, '', 1);
        }
    }

    public function clear(): void
    {
        $_SESSION[PREFIX] = [];
    }

    public function setAdmin(): self
    {
        if (!is_null($this->get(self::AUTH_KEY))) {
            $this->set(self::AUTH_ADMIN, true);
        } else {
            $this->set(self::AUTH_ADMIN, false);
        }
        return $this;
    }

    public function isAdmin(): bool
    {
        return $this->get(self::AUTH_ADMIN) ?? false;
    }
    
    /**
     * Retrieves the role ID related to the authenticated User.
     *
     * @return string|null Returns the role if available, or null if not set.
     */
    public function getRole(): ?string
    {
        return $this->get(self::AUTH_ROLE) ?? null;
    }
    
    /**
     * Retrieves the role weight value associated with the authenticated user's role.
     *
     * @return int The weight value, or 24 if no value is set.
     */
    public function getWeight(): int
    {
        return $this->get(self::AUTH_ROLE_WEIGHT) ?? 24;
    }
    
    /**
     * Retrieves the permission associated with the current authenticated users role and permission.
     *
     * @return string|null The permission string if available, or null if not set.
     */
    public function getPermission(): ?string
    {
        return $this->get(self::AUTH_PERMISSION) ?? null;
    }
    
    /**
     * Retrieves the bitwise value associated with authenticated user and role from the user's permissions.
     * If the key does not exist, a default value of 24 is returned.
     *
     * @return int The bitwise value or the default value of 24.
     */
    public function getBitwise(): int
    {
        return $this->get(self::AUTH_BITWISE) ?? 24;
    }
}


