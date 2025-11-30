<?php

namespace JDS\Infrastructure;

use Doctrine\DBAL\Connection;
use JDS\Contracts\Security\TokenStoreInterface;
use JDS\Dbal\AbstractDatabaseHelper;

class DatabaseTokenStore extends AbstractDatabaseHelper implements TokenStoreInterface
{
    public function __construct(private Connection $db)
    {
    }

    public function store(string $tokenId, int $expiredAt): void
    {
        $sql = "INSERT INTO single_use_tokens 
                (
                    token_id,
                    expires_at
                )
                VALUES
                (
                 :tokenId,
                 :expiredAt
                ); ";
        $stmt = $this->db->prepare($sql);
        $this->bind($stmt, 'tokenId', $tokenId);
        $this->bind($stmt, 'expiredAt', $expiredAt);
        $stmt->executeStatement();
    }

    private function toTimestamp(\DateTimeInterface $dt): int
    {
        return $dt->getTimestamp();
    }
    public function isUsed(string $tokenId): bool
    {
        $sql = "SELECT
                    used
                FROM
                    single_use_tokens
                WHERE
                    token_id = :tokenId; ";
        $stmt = $this->db->prepare($sql);
        $this->bind($stmt, 'tokenId', $tokenId);
        $value = $stmt->executeQuery()->fetchOne();
        return (bool)$value;
    }

    public function markUsed(string $tokenId): void
    {
        $sql = "UPDATE single_use_tokens SET
                     used  = :used
                WHERE
                    token_id = :tokenId; ";
        $stmt = $this->db->prepare($sql);
        $this->bind($stmt, 'tokenId', $tokenId);
        $stmt->executeStatement();
    }

    public function purgeExpired(): int
    {
        $sql = "DELETE FROM
                    single_use_tokens
                WHERE
                    expires_at < :expiredAt; ";
        $stmt = $this->db->prepare($sql);
        $this->bind($stmt, 'expiredAt', time());
        return $stmt->executeStatement();
    }
}

