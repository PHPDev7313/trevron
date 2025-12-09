<?php

namespace JDS\Http\Listener;

use JDS\Configuration\Config;
use JDS\Http\Event\TerminateEvent;

final class TerminateActivityListener
{
    public function __construct(
        private Config $config // <-- inject it!
    )
    {
    }

    public function __invoke(TerminateEvent $event)
    {
        $req = $event->getRequest();
        $res = $event->getResponse();

        $userId = $req->getSession()::AUTH_KEY;
        if (!$userId) {
            return;
        }

        //
        // This can later be replaced with a proper ActivityLogWriter interface.
        // __DIR__ . '/../../../../../storage/activity.log',
        //
        file_put_contents(
            '/' . $this->config->get('activityPath') . '/' . $this->config->get('activityFile'),
            sprintf(
                "[%s] user=%s uri=%s\n",
                date('Y-m-d H:i:s'),
                $userId,
                $req->getUri()
            ),
            FILE_APPEND
        );
    }
}

