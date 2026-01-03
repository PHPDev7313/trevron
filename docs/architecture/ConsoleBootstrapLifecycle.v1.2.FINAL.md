# Console Bootstrap Lifecycle - v1.2 FINAL

Project: Trevron Framework

Document: Console Bootstrap Lifecycle — v1.2 FINAL

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: January 3, 2026

Owner: Mr J (Jessop Digital Systems)

© 2026 Jessop Digital Systems

---

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

## Console Execution Modes

The Console runtime operates in one of two **explicit modes**:

### 1. Secrets Tooling Mode (`secrets:*`)

Triggered when:

```
argv[1] starts with "secrets:"
```

**Characteristics**

* Secrets schema and crypto configuration are required
* Runtime `SecretsInterface` MUST NOT be resolved
* Encrypted secrets file MAY be absent
* Commands operate directly on secrets tooling

This mode exists to allow:

* Encryption
* Decryption
* Validation
* Editing

Without requiring runtime secret availability.

---

### 2. Runtime Command Mode (Default)

Triggered when command is **not** `secrets:*`.

**Characteristics**

* Encrypted secrets file MUST exist
* Secrets MUST be decrypted, validated, and locked
* Database access is permitted
* Runtime commands may execute

Failure to distinguish these modes is a **security violation**.

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

**Additional Guarantees**

* Required environment variables are present and non-empty
* Invalid or missing `.env` values cause immediate failure

Required Console variables include (non-exhaustive):

* `APP_ENV`
* `APP_SECRET_KEY`
* `SECRETS_FILE`
* `SECRETS_PLAIN`
* `SCHEMA_FILE`

**Violations**

* Missing config → fatal
* Resolution during CONFIG → fatal
* Missing required environment variables → fatal

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

## Non-Repeatable Phase Rule

The following phase is **explicitly non-repeatable**:

* `BootstrapPhase::COMMANDS`

**Reason**

* Command registration must be deterministic
* Duplicate registration introduces security ambiguity

BootstrapRunner enforces:

* No duplicate COMMANDS phase
* No late registration

---

## Console-Only Rules

The Console runtime differs from HTTP in critical ways:

| Aspect            | HTTP   | Console      |
|-------------------|--------|--------------|
| Secrets optional  | Yes    | **No**       |
| Commands phase    | No     | **Yes**      |
| Registry locking  | N/A    | **Required** |
| Direct execution  | No     | **Yes**      |

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

## Bootstrap Verification

The Console runtime provides a mandatory verification command:

```
php bin/console bootstrap:verify
```

**Purpose**

* Validate all Console bootstrap invariants
* Ensure container correctness
* Detect misconfiguration early

**Characteristics**

* Read-only
* No state mutation
* No command execution
* No side effects

**Failure Behavior**

Any invariant violation causes:

* Immediate failure
* Non-zero exit code
* No runtime execution

This command is intended for:

* CI pipelines
* Deployment checks
* Developer diagnostics

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

## Failure Semantics

On any bootstrap invariant violation:

* Execution MUST halt immediately
* No command execution may occur
* Exit code MUST be non-zero
* Partial container state MUST NOT persist

Silent degradation is forbidden.

---

## Summary (Hard Rules)

* Console **always** runs `CONFIG → SECRETS → COMMANDS`
* SECRETS is **never optional** in Console
* COMMANDS is **final and immutable**
* Phases are **instructions**, not services
* Kernel executes only after invariants are satisfied

This lifecycle is frozen for v1.2 FINAL.

Any change requires a version bump and architecture review.
