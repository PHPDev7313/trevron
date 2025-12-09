<?php

namespace JDS\Http\Listener;



use JDS\Configuration\Config;
use JDS\Http\Event\TerminateEvent;
use Psr\Log\LoggerInterface;

final class TerminateLoggingListener
{
    public function __construct(
        private LoggerInterface $logger,
        private Config $config
    ) {}

    public function __invoke(TerminateEvent $event): void
    {
        $req = $event->getRequest();
        $res  = $event->getResponse();

        $this->logger->info('HTTP Request Completed', [
            'method' => $req->getMethod(),
            'url'    => $req->getUri(),
            'status' => $res->getStatus(),
            'duration_ms' => round($event->getDuration() * 1000, 2),
            'log_path' => "/" . $this->config->get('httpLogPath') . '/' . $this->config->get('httpLogFile'),
        ]);
    }
}


