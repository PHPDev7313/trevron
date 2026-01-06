<?php

namespace JDS\Authentication;

use JDS\Exceptions\Http\FileWriteException;
use JDS\Handlers\ExceptionHandler;
use Throwable;

class AbstractSession
{
    protected function resetCookie(): void
    {
//        $isProd = (($_ENV['APP_ENV'] ?? null) === null) || ($_ENV['APP_ENV'] === 'production');
        try {
            // Invalidate the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    (time() - 42000),
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            foreach ($_COOKIE as $key => $value) {
                setcookie($key, '', 1);
            }
        } catch (FileWriteException $e) {
            ExceptionHandler::render($e, "Failed to write to file");
        } catch (Throwable $e) {
            ExceptionHandler::render($e, "An unexpected error has occurred.");
        }
    }
}

