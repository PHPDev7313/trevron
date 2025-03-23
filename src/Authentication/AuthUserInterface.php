<?php

namespace JDS\Authentication;

interface AuthUserInterface
{
	public function getAuthId(): int|string;

	public function getEmail(): string;

	public function getPassword(): string;

    public function getRoleId(): ?string;

    public function getPermissionId(): ?string;

    public function getBitwise(): ?int;

    public function getRoleWeight(): ?int;

    public function isAdmin(): bool;

}
