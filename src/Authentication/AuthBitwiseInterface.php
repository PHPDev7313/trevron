<?php

namespace JDS\Authentication;

interface AuthBitwiseInterface
{
    // this is the bitwise value from the permission
    public function getBitwise(): int;

}

