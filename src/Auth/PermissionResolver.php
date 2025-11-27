<?php

namespace JDS\Auth;

use Doctrine\DBAL\Connection;
use JDS\Contracts\Auth\ResolverInterface;
use JDS\Dbal\AbstractDatabaseHelper;
use Predis\Client as PredisClient;

class PermissionResolver extends AbstractDatabaseHelper implements ResolverInterface
{

    public function __construct(
        private Connection    $connection,
        private ?PredisClient $redis = null,
        private int           $ttl = 300
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function resolvePermissions(string $companyId, string $userId, ?string $roleId = null): array
    {
        $perms = [];

        // use cache only when roleId present
        if ($roleId && $this->redis) {
            $cacheKey = sprintf('perms:%s:%s', $companyId, $roleId);
            $cached = $this->redis->get($cacheKey);
            if ($cached !== null) {
                return json_decode($cached, true);
            }
        }

        // 1) load role permission for role(s) attached to the company.
        if ($roleId) {
            $sql = "SELECT
                        p.name
                    FROM
                        role_permission rp 
                    JOIN 
                        permissions p 
                    ON 
                        rp.permission_id = p.permission_id
                    WHERE
                        rp.role_id = :roleId; ";
            $stmt = $this->connection->prepare($sql);
            $this->bind($stmt, 'roleId', $roleId);
            $results = $stmt->executeQuery();
            $rows = $results->fetchFirstColumn();
            foreach ($rows as $name) {
                $perms[$name] = true;
            }
        } else {
            $sql = "SELECT
                        cr.role_id
                    FROM
                        company_user cu
                    JOIN 
                        company_role cr ON cr.company_id = cu.company_id                    
                    WHERE 
                        cu.user_id = :userId
                    AND
                        cu.company_id = :companyId
                    LIMIT 1; ";
            $stmt = $this->connection->prepare($sql);
            $this->bind($stmt, 'userId', $userId);
            $this->bind($stmt, 'companyId', $companyId);
            $results = $stmt->executeQuery();
            $found = $results->fetchOne();
            if ($found !== false && $found !== null) {
                return $this->resolvePermissions($companyId, $userId, $found);
            }
        }
        // 2) Add explicit user permissions (overrides)
        $sql = "SELECT
                    p.name
                FROM
                    user_permission up
                JOIN
                    permissions p 
                ON 
                    up.permission_id = p.permission_id
                WHERE
                    up.user_id = :userId; ";
        $stmt = $this->connection->prepare($sql);
        $this->bind($stmt, 'userId', $userId);
        $results = $stmt->executeQuery();
        $rows = $results->fetchFirstColumn();
        foreach ($rows as $name) {
            $perms[$name] = true;
        }

        $result = array_values(array_keys($perms));

        // write cache if available
        if ($roleId && $this->redis) {
            $this->redis->setex($cacheKey, $this->ttl, json_encode($result));
        }

        return $result;
    }

    /**
     * Redis delete cache
     *
     * @param string $companyId
     * @param string $roleId
     * @return void
     */
    public function invalidateCacheForRole(string $companyId, string $roleId): void
    {
        if (!$this->redis) return;
        $cacheKey = sprintf('perms:%s:%s', $companyId, $roleId);
        $this->redis->del($cacheKey);
    }
}

