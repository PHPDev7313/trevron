<?php

namespace JDS\Contracts\Authentication;

interface AuthRepositoryInterface
{
	public function findByEmail(string $email): ?AuthUserInterface;
}

