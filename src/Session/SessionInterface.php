<?php

namespace JDS\Session;

interface SessionInterface
{
	public function start(): void;
	public function set(string $key, $value): void;

	public function get(string $key, $default = null);

	public function has(string $key): bool;

	public function remove(string $key): void;

	public function getFlash(string $key): array;

	public function setFlash(string $type, string $message): void;

	public function hasFlash(string $type): bool;

	public function clearFlash(): void;

    public function setAdmin(): self;

    public function isAdmin(): bool;

	public function isAuthenticated(): bool;

}

