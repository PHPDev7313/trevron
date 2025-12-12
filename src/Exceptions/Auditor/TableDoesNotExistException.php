<?php

namespace JDS\Exceptions\Auditor;

use Exception;

class TableDoesNotExistException extends Exception
{

}

//CREATE TABLE logs (
//    id INT(12) UNSIGNED AUTO_INCREMENT,
//    log_id varbinary(12) NOT NULL,
//    level VARCHAR(10) NOT NULL,        -- Severity level (e.g., "INFO", "ERROR")
//    message TEXT NOT NULL,             -- The main log message
//    context JSON DEFAULT NULL,         -- Additional context as JSON
//    created DATETIME DEFAULT CURRENT_TIMESTAMP -- Timestamp of the log,
//    PRIMARY KEY (log_id),
//    UNIQUE KEY id (id),
//    KEY level (level)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
