<?php

namespace JDS\Contracts\Bootstrap;

interface BootstrapAwareContainerInterface
{
    public function enterBootstrap(): void;
    public function exitBootstrap(): void;

}

