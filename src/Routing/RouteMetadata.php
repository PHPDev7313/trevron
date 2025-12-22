<?php
/*
 * Trevron Framework — v1.2 FINAL
 *
 * © 2025 Jessop Digital Systems
 * Date: December 19, 2025
 *
 * This file is part of the v1.2 FINAL architectural baseline.
 * Changes require an architecture review and a version bump.
 *
 * See: RoutingFINALv12ARCHITECTURE.md
 */

namespace JDS\Routing;

use JDS\Error\StatusCode;
use JDS\Exceptions\Error\StatusException;

class RouteMetadata
{
    public function __construct(
        public readonly ?string $label,
        public readonly ?string $path,
        public readonly bool    $requiresToken
    )
    {}

    public static function fromArray(array $meta): self
    {
        //
        // Allowed keys
        //
        $allowed = ['label', 'path', 'requires_token'];

        //
        // Reject unknown keys
        //
        foreach ($meta as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                throw new StatusException(
                    StatusCode::ROUTE_METADATA_INVALID,
                    "Unknown metadata key '{$key}' in route metadata."
                );
            }
        }

        //
        // Validate types
        //
        if (isset($meta['label']) && !is_string($meta['label'])) {
            throw new StatusException(
                StatusCode::ROUTE_METADATA_INVALID,
                "Metadata 'label' must be a string."
            );
        }

        if (isset($meta['path']) && !is_string($meta['path']) && $meta['path'] !== null) {
            throw new StatusException(
                StatusCode::ROUTE_METADATA_INVALID,
                "Metadata 'path' must be string or null."
            );
        }

        if (isset($meta['requires_token']) && !is_bool($meta['requires_token'])) {
            throw new StatusException(
                StatusCode::ROUTE_METADATA_INVALID,
                "Metadata 'requires_token' must be boolean."
            );
        }

        //
        // Build value object with defaults
        //
        return new self(
            label: $meta['label'] ?? null,
            path: $meta['path'] ?? null,
            requiresToken: $meta['requires_token'] ?? false
        );
    }
}

