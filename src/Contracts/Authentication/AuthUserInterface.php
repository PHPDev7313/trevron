<?php

namespace JDS\Contracts\Authentication;

interface AuthUserInterface
{
	public function getAuthId(): string;

    public function getCompanyId(): string;

	public function getEmail(): string;

	public function getPassword(): string;

    public function getRoleId(): string;

    public function getPermisisonId(): string;

    public function getAccessLevel(): int;

    public function isAdmin(): bool;
}

