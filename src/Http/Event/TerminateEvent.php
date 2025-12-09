<?php

namespace JDS\Http\Event;

use JDS\EventDispatcher\Event;
use JDS\Http\Request;
use JDS\Http\Response;

class TerminateEvent extends Event
{
    public function __construct(
        private Request $request,
        private Response  $response,
        private float $startTime,
        private float $endTime,
        private float $duration
    )
    {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getEndTime(): float
    {
        return $this->endTime;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }
}



