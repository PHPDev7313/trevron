<?php

namespace JDS\Http\Middleware;

use Exception;
use Throwable;

class SessionStartException extends Exception
{
    public function __construct(string $message = "An error occured while starting the sesison.", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}