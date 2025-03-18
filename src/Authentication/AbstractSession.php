<?php

namespace JDS\Authentication;

use JDS\Handlers\ExceptionHandler;
use JDS\Http\FileWriteException;
use Throwable;

class AbstractSession
{
    protected function resetCookie(): void
    {
        $isProd = (($_ENV['APP_ENV'] ?? null) === null) || ($_ENV['APP_ENV'] === 'production');
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

//    keep this for now for reference
//    protected function configuration(): void
//    {
//        // Set session save path.
//        $savePath = ini_get('session.save_path');
//
////        // Ensure the session save path exists.
////        if (!is_dir($savePath) && !mkdir($savePath, 0777, true) && !is_dir($savePath)) {
////            // If the directory cannot be created, throw an error.
////            throw new RuntimeException('Failed to create session save path: ' . $savePath);
////        }
////
////        // Ensure the session save path is writable.
////        if (!is_writable($savePath)) {
////            throw new RuntimeException('Session save path is not writable: ' . $savePath);
////        }
//
//        // Apply the session save path.
//        session_save_path($savePath);
//
//        // Set session cookie parameters.
//    }
