<?php

namespace JDS\Contracts\Logging;

use JDS\Logging\Entity\EntityActivityRecord;

interface EntityActivityLogWriterInterface
{
    public function write(EntityActivityRecord $record): void;
}