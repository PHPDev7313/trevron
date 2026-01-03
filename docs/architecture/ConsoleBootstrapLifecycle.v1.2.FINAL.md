# Architecture Specification

Project: Trevron Framework

Document: Console Bootstrap Lifecycle — v1.2 FINAL

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: January 3, 2026

Owner: Mr J (Jessop Digital Systems)

© 2026 Jessop Digital Systems

## Overview

This document defines the **non-negotiable bootstrap invariants** for the **Console runtime** in Trevron v1.2 FINAL.

Console execution is **stateful, privileged, and destructive by nature** (database access, secrets mutation, migrations). For that reason, the console bootstrap lifecycle is **stricter** than HTTP.

This document is authoritative.

---

## Console Invariant Set

The Console runtime enforces the following invariant phase set:

```
CONFIG  →  SECRETS  →  COMMANDS
```

These phases are:

* **Required**
* **Ordered**
* **Non-repeatable**
* **Console-only**

Failure to satisfy any invariant results in a **hard bootstrap failure**.

---

## Phase Definitions

### 1. CONFIG (Required)

**Purpose**

* Load application configuration
* Bind immutable configuration into the container

**Guarantees after phase**

* `config` exists and is immutable
* Environment paths are resolved
* No secrets resolved
* No commands registered

**Violations**

* Missing config → fatal
* Resolution during CONFIG → fatal

---

### 2. SECRETS (Required — NEVER optional for Console)

**Purpose**

* Unlock encrypted secrets
* Validate against schema
* Enforce cryptographic readiness

**Why required**
Console commands:

* Require database credentials
* Perform encryption/decryption
* Must validate and edit secrets

Skipping secrets in Console is a **security violation**.

**Guarantees after phase**

* `SecretsInterface` is resolvable
* Secrets are validated and immutable
* Crypto extension requirements verified

**Violations**

* Missing secrets config → fatal
* Schema mismatch → fatal
* Secrets resolution before phase → fatal

---

### 3. COMMANDS (Required — FINAL PHASE)

**Purpose**

* Register all console commands
* Lock the command registry

**Design constraints**

* Commands are registered **only during this phase**
* Registry becomes immutable after phase

**Guarantees after phase**

* `CommandRegistryInterface` is locked
* Command set is complete and immutable
* Kernel may safely dispatch commands

**Violations**

* Registering commands after phase → fatal
* Duplicate COMMANDS phase → fatal

---

## Non‑Repeatable Phase Rule

The following phase is **explicitly non-repeatable**:

* `BootstrapPhase::COMMANDS`

Reason:

* Command registration must be deterministic
* Duplicate registration introduces security ambiguity

BootstrapRunner enforces:

* No duplicate COMMANDS phase
* No late registration

---

## Console‑Only Rules

The Console runtime differs from HTTP in critical ways:

| Aspect            | HTTP  | Console       |
|-------------------|-------|---------------|
| Secrets optional  | Yes   | **No**        |
| Commands phase    | No    | **Yes**       |
| Registry locking  | N/A   | **Required**  |
| Direct execution  | No    | **Yes**       |

---

## Responsibilities by Component

### `app.console.php`

* Declares required invariant set
* Registers kernel prerequisites
* Executes bootstrap runner

### `BootstrapRunner`

* Enforces phase order
* Enforces phase uniqueness
* Locks command registry

### `CommandPhase`

* Finalizes command registration
* Locks registry permanently

### `CommandRegistry`

* Accepts registrations pre-lock
* Dispatches commands post-lock

---

## Forbidden Actions

The following are architectural violations:

* Skipping SECRETS in Console
* Resolving secrets during CONFIG
* Registering commands after COMMANDS
* Re-running COMMANDS phase
* Resolving bootstrap phases from container

Each violation must raise a **BootstrapInvariantViolationException**.

---

## Summary (Hard Rules)

* Console **always** runs CONFIG → SECRETS → COMMANDS
* SECRETS is **never optional** in Console
* COMMANDS is **final and immutable**
* Phases are **instructions**, not services
* Kernel executes only after invariants are satisfied

This lifecycle is frozen for v1.2 FINAL.

Any change requires a version bump and architecture review.

