<?php

namespace JDS\Auth\Listener;

use Doctrine\DBAL\Connection;
use JDS\Auth\Event\UserLoggedIn;
use JDS\Dbal\AbstractDatabaseHelper;

class UserLoggedInListener extends AbstractDatabaseHelper
{
    public function __construct(private Connection $connection) {}

    public function __invoke(UserLoggedIn $event): void
    {
        $sql = "INSERT INTO 
                    auth_login_audit 
                    (
                     user_id,
                     company_id,
                     permissions,
                     logged_at
                    )
                VALUES
                    (
                     :userId,
                     :companyId,
                     :permissions,
                     :loggedAt
                    ); ";
        $stmt = $this->connection->prepare($sql);
        $this->bind($stmt, 'userId', $event->userId);
        $this->bind($stmt, 'companyId', $event->companyId);
        $this->bind($stmt, 'permissions', $event->permissions);
        $this->bind($stmt, 'loggedAt', $event->when->format('Y-m-d H:i:s'));
        $stmt->executeStatement();
    }
}

