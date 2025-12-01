<?php

namespace JDS\Console\Command;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Security\TokenStoreInterface;

class PurgeExpiredTokens implements CommandInterface
{

    public function __construct(private TokenStoreInterface $store)
    {
    }

    public function getName(): string
    {
        return 'tokens:purge';
    }

    public function execute(array $params = []): int
    {
        $count = $this->store->purgeExpired();

        echo "Purged $count expired token(s).\n";

        return 0;
    }
}

