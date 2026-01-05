# Console Bootstrap Lifecycle — v1.2.1 FINAL

Project: **Trevron Framework**

Document: **Console Bootstrap Lifecycle — v1.2.1 FINAL**

Version: **v1.2.1 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: January 3, 2026

Owner: Mr J (Jessop Digital Systems)

© 2026 Jessop Digital Systems

---

## Overview

This document defines the **non-negotiable bootstrap invariants** for the **Console runtime** in Trevron v1.2 FINAL.

Console execution is **stateful, privileged, and destructive by nature** (database access, encryption, secrets mutation, migrations).
For that reason, the Console bootstrap lifecycle is **strict**, **ordered**, and **mode-aware**.

This document is authoritative.

---

## Console Invariant Set

The Console runtime enforces the following invariant phase set:

CONFIG  →  SECRETS  →  COMMANDS

These phases are:

* **Required**
* **Ordered**
* **Non-repeatable**
* **Console-only**

Failure to satisfy any invariant results in a **hard bootstrap failure**.

---

## Console Secrets Modes (Critical)

The Console runtime operates in **one of two mutually exclusive secrets modes**.

This distinction is **intentional** and **security-critical**.

---

### 🔐 Mode A — Secrets Tooling Mode (`secrets:*`)

Activated when:

```
argv[1] starts with "secrets:"
```

Examples:

* `secrets:encrypt`
* `secrets:decrypt`
* `secrets:edit`
* `secrets:validate`

#### Purpose

Secrets tooling commands:

* Create encrypted secrets
* Edit plaintext secrets
* Validate schemas
* Operate **before runtime secrets may exist**

#### Guarantees

* `SecretsConfigInterface` **MUST exist**
* Crypto extension availability is enforced
* Schema files and paths are validated
* Command registry is available

#### Explicitly Forbidden

* `SecretsInterface` **MUST NOT be registered**
* `SecretsServiceProvider` **MUST NOT be loaded**
* Encrypted secrets **MUST NOT be resolved**

Resolving runtime secrets during tooling is a **security violation**.

#### Rationale

Tooling commands **construct secrets** — they cannot depend on them.

---

### 🔐 Mode B — Runtime Secrets Mode (All Other Console Commands)

Activated when:

```
command is NOT secrets:*
```

Examples:

* `migrate`
* `bootstrap:verify`
* `bootstrap:dump`
* application maintenance commands

#### Guarantees

* `SecretsServiceProvider` **MUST be registered**
* `SecretsInterface` **MUST resolve successfully**
* Secrets are:

    * decrypted
    * schema-validated
    * immutable
* Crypto readiness is verified

#### Violations

* Missing secrets → fatal
* Invalid schema → fatal
* Access before SECRETS phase → fatal

---

## Phase Definitions

---

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

### 2. SECRETS (Required — Mode-Aware)

**Purpose**

* Establish secrets **capability**, not behavior
* Enforce cryptographic readiness

#### Tooling Mode Behavior

* `SecretsConfigInterface` must exist
* Runtime secrets must **not** be constructed

#### Runtime Mode Behavior

* `SecretsServiceProvider` must be loaded
* `SecretsInterface` must resolve successfully
* Secrets become immutable

**Violations**

* Runtime secrets resolved during tooling → fatal
* Runtime command without secrets → fatal
* Schema mismatch → fatal

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
* Runtime secrets missing → fatal

---

## Non-Repeatable Phase Rule

The following phase is **explicitly non-repeatable**:

* `BootstrapPhase::COMMANDS`

Reason:

* Command registration must be deterministic
* Duplicate registration introduces ambiguity and security risk

---

## SecretsServiceProvider (Authoritative Rule)

`SecretsServiceProvider` is responsible for **constructing runtime secrets**.

### Rules

* **MUST be loaded** in:

    * Runtime Secrets Mode
* **MUST NOT be loaded** in:

    * Secrets Tooling Mode

### Responsibilities

* Registers `SecretsInterface`
* Enforces:

    * crypto readiness
    * schema validity
    * immutable secrets state
* Guards against resolution during bootstrap

Violating these rules is a **bootstrap invariant failure**.

---

## Console-Only Rules

| Aspect                | HTTP  | Console       |
|-----------------------|-------|---------------|
| Secrets optional      | Yes   | **No**        |
| Secrets tooling mode  | No    | **Yes**       |
| Command registry      | No    | **Yes**       |
| Registry locking      | N/A   | **Required**  |
| Direct execution      | No    | **Yes**       |

---

## Responsibilities by Component

### `app.console.php`

* Determines secrets mode
* Declares required invariant set
* Registers kernel prerequisites
* Executes bootstrap runner

### `SecretsServiceProvider`

* Constructs runtime secrets
* Enforces crypto + schema invariants
* Must respect secrets mode

### `CommandPhase`

* Enforces mode-correct secrets invariants
* Locks registry permanently

### `CommandRegistry`

* Accepts registrations pre-lock
* Dispatches commands post-lock

---

## Forbidden Actions (Hard Failures)

* Skipping SECRETS in Console
* Resolving runtime secrets during tooling
* Registering commands after COMMANDS
* Re-running COMMANDS phase
* Loading `SecretsServiceProvider` in tooling mode
* Accessing bootstrap phases via container

Each violation must raise a **BootstrapInvariantViolationException**.

---

## Summary (Hard Rules)

* Console always runs `CONFIG → SECRETS → COMMANDS`
* SECRETS is **never optional**
* Secrets behavior is **mode-aware**
* `SecretsServiceProvider` is runtime-only
* COMMANDS is final and immutable
* Phases are **instructions**, not services

This lifecycle is frozen for v1.2 FINAL.

Any change requires a version bump and architecture review.

---

# ✅ Validation Checklist (for your current code)

You are correct **if all are true**:

✔ `console.is_secrets_command` is registered **before bootstrap**
✔ `services/secrets.php` returns early in tooling mode
✔ `SecretsServiceProvider` is only registered in runtime mode
✔ `CommandPhase` enforces secrets **based on mode**
✔ `SecretsInterface` is never faked or stubbed
✔ Registry is locked exactly once

You are **now aligned with the document** and the framework’s intent.
