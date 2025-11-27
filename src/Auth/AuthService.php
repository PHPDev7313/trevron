<?php

namespace JDS\Auth;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use JDS\Auth\Event\UserLoggedIn;
use JDS\Contracts\Auth\AuthServiceInterface;
use JDS\Contracts\Session\SessionInterface;
use JDS\Dbal\AbstractDatabaseHelper;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AuthService extends AbstractDatabaseHelper implements AuthServiceInterface
{

    public function __construct(
        private Connection $connection,
        private PermissionResolver $resolver,
        private SessionInterface $session,
        private ?EventDispatcherInterface $dispatcher = null,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function login(string $email, string $password, ?string $companyId = null): bool
    {
        $sql = "SELECT
                    *
                FROM
                    users
                WHERE
                    email = :email
                LIMIT 1; ";
        $stmt = $this->connection->prepare($sql);
        $this->bind($stmt, 'email', $email);
        $result = $stmt->executeQuery();
        $user = $result->fetchAssociative();
        if (!$user) return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        if ($companyId === null) {
            $sql = "SELECT
                        company_id
                    FROM
                        company_user
                    WHERE
                        user_id = :userId
                    LIMIT 1; ";
            $stmt = $this->connection->prepare($sql);
            $this->bind($stmt, 'userId', $user['user_id']);
            $result = $stmt->executeQuery();
            $ok = $result->fetchOne();
            if ($ok === false) return false;
        }
        $sql = "SELECT
                    cr.role_id,
                    r.access_level
                FROM
                    company_user cu
                JOIN 
                    company_role
                ON 
                    cr.company_id = cu.company_id
                JOIN 
                    roles r
                ON 
                    r.role_id = cr.role_id
                WHERE
                    cu.company_id = :companyId
                AND
                    cu.user_id = :userId
                LIMIT 1; ";
        $stmt = $this->connection->prepare($sql);
        $this->bind($stmt, 'companyId', $companyId);
        $this->bind($stmt, 'userId', $user['user_id']);
        $result = $stmt->executeQuery();
        $roleRow = $result->fetchAssociative();
        $roleId = $roleRow['role_id'] ?? null;
        $accessLevel = isset($roleRow['access_level']) ? (int)$roleRow['access_level'] : 0;

        $this->session->start();
        $this->session->set($this->session::AUTH_KEY, $user['user_id']);
        $this->session->set($this->session::AUTH_ROLE, $roleId);
        $this->session->set($this->session::AUTH_ACCESS_LEVEL, $accessLevel);
        $this->session->set($this->session::AUTH_PERMISSION, $user['permission_id'] ?? null);
        $this->session->set($this->session::AUTH_ADMIN, (bool)($user['is_admin'] ?? 0));

        $resolved = $this->resolver->resolvePermissions($companyId, $user['user_id'], $roleId);
        $this->session->set('auth_permissions_list', $resolved);
        $this->session->set('auth_company_id', $companyId);

        $this->dispatcher?->dispatch(new UserLoggedIn($user['user_id'], $companyId, $resolved, new DateTimeImmutable()));

        return true;

    }

    public function logout(): void
    {
        $this->session->remove($this->session::AUTH_KEY);
        $this->session->remove($this->session::AUTH_ACCESS_LEVEL);
        $this->session->remove($this->session::AUTH_PERMISSION);
        $this->session->remove($this->session::AUTH_ROLE);
        $this->session->remove($this->session::AUTH_ADMIN);
        $this->session->remove($this->session::AUTH_PERMISSION_LIST);
        $this->session->remove($this->session::AUTH_COMPANY);
    }
}

