# Infrastructure Services — v1.2.0 FINAL

**Jessop Digital Systems**

Project: Trevron Framework

Document: **Infrastructure Services — v1.2.0 FINAL**

Version: **v1.2.0 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: January 7, 2026

Owner: Mr J (Jessop Digital Systems)

© 2026 Jessop Digital Systems

---

## Purpose

This document defines the **Infrastructure Services layer** in the v1.2 FINAL architecture. Infrastructure services are responsible for interacting with **external systems** and **environment-dependent resources**. They are intentionally isolated from Core services and loaded only after secrets have been unlocked and validated.

This layer exists to:

* Enforce strict bootstrap ordering
* Prevent side effects during core initialization
* Centralize I/O-bound and environment-sensitive services
* Provide a stable contract for future infrastructure growth

---

## What Qualifies as an Infrastructure Service

A service **belongs in Infrastructure** if **any** of the following are true:

* Depends on environment variables or decrypted secrets
* Performs I/O (network, filesystem, sockets)
* Communicates with external systems
* May fail due to external availability or permissions
* Must not be initialized during dry-runs or command discovery

If a service can fail because the environment is wrong, it **does not belong in Core**.

---

## Load Order Invariants

Infrastructure services **must** be loaded in the following order:

1. `services/core.php`
2. `services/secrets.php`
3. `services/infrastructure.php`
4. Runtime layer:

    * `services/http.php` **or**
    * `services/console.php`

Violating this order is considered an **architectural error** and must result in a hard failure.

---

## Required Guards

Every infrastructure bootstrap **must** enforce:

* Secrets have been unlocked and validated
* Configuration is present and structurally valid
* Providers fail loudly and early on misconfiguration

Silent failure is not permitted in this layer.

---

## Current Infrastructure Providers

### DatabaseConnectionServiceProvider

**Responsibilities:**

* Read database configuration from validated config
* Validate required credentials
* Register `Doctrine\\DBAL\\Connection`
* Establish connections lazily

**Must NOT:**

* Read directly from `$_ENV`
* Be registered in `core.php`
* Swallow connection or configuration errors

---

## Future Infrastructure Providers (Planned)

### Filesystem Adapters

Examples:

* Local filesystem adapters
* Encrypted file stores
* Cloud object storage (S3-compatible)

Characteristics:

* Environment-dependent paths or credentials
* I/O and permission sensitive
* May fail due to missing directories or access rights

---

### Mail Transport

Examples:

* SMTP transport
* Sendmail
* API-based mail services

Characteristics:

* Depends on secrets
* External network I/O
* Environment-specific

Mail transports **must not** initialize during command listing or verification modes.

---

### Cache Clients

Examples:

* Redis
* Memcached
* APCu

Characteristics:

* Optional in some environments
* External services or PHP extensions
* May not exist in all runtimes

Cache services should be:

* Explicitly registered
* Fail-fast when required
* Optional where appropriate

---

## Architectural Enforcement

Infrastructure services are not optional conveniences — they are **explicit system dependencies**.

All infrastructure providers:

* Must live outside Core
* Must be registered intentionally
* Must fail early when invariants are broken

This guarantees predictable startup behavior and prevents hidden side effects during runtime discovery.
