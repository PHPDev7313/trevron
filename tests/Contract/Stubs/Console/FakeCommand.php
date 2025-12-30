<?php

namespace Tests\Contract\Stubs\Console;

use JDS\Contracts\Console\Command\CommandInterface;

class FakeCommand implements CommandInterface
{

    public function execute(array $params = []): int
    {
        return 0;
    }
}


