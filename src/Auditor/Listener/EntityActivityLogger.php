<?php

namespace JDS\Auditor\Listener;

class EntityActivityLogger
{
    public function __construct(
        private DiffServiceInterface $diffService,
    )
    {
    }
}