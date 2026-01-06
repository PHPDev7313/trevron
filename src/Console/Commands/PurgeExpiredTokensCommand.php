<?php

namespace JDS\Console\Commands;

use JDS\Contracts\Console\Command\CommandInterface;
use JDS\Contracts\Security\TokenStoreInterface;

class PurgeExpiredTokensCommand implements CommandInterface
{
    protected string $name = "purge:expired:tokens";

    protected string $description = "Purge expired tokens";
    public function __construct(
        private TokenStoreInterface $tokens,
    )
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
        $count = $this->tokens->purgeExpired();

        // Print user-facing output
        echo "Purged {$count} expired single-use token(s)." . PHP_EOL;

        return 0; // 0 = success
    }
}

