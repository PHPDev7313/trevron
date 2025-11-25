<?php

namespace JDS\Contracts\Console\Command;

interface CommandInterface
{
	public function execute(array $params = []): int;
}

