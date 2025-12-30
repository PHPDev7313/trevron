<?php

namespace JDS\Contracts\Dbal\Migration;

use JDS\Console\BaseCommand;

final class MigrateDatabase extends BaseCommand
{
    public function __construct(
        private MigrationRunner $runner
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(array $params = []): int
    {
        return $this->runner->run($params);
    }
}