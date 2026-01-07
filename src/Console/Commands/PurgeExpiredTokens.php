<?php

namespace JDS\Console\Commands;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Security\TokenStoreInterface;

class PurgeExpiredTokens implements CommandInterface
{

    protected string $name = 'tokens:purge';
    protected string $description = 'Purge expired tokens';
    public function __construct(private TokenStoreInterface $store)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function execute(array $params = []): int
    {
        $count = $this->store->purgeExpired();

        echo "Purged $count expired token(s).\n";

        return 0;
    }
}

