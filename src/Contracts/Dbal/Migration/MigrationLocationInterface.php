<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 31, 2025
 *
 * Contract for migration discovery and numeric ordering.
 */

declare(strict_types=1);

namespace JDS\Contracts\Dbal\Migration;

interface MigrationLocationInterface
{
    /**
     * Discover all migration filenames ordered numerically.
     *
     * @return list<string> Ordered migratoin filenames
     */
    public function all(): array;
}

