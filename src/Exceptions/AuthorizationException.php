<?php

namespace JDS\Exceptions;

class AuthorizationException extends \Exception
{
    public function __construct(string $message = "Access denied")
    {
        parent::__construct($message, 403);
    }
}

