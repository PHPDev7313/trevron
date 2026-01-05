# Console Bootstrap Lifecycle — v1.2.2 FINAL

Project: Trevron Framework

Document: **Console Bootstrap Lifecycle — v1.2.2 FINAL**

Version: **v1.2.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: January 3, 2026

Owner: Mr J (Jessop Digital Systems)

© 2026 Jessop Digital Systems

---

## Overview

This document defines the **non‑negotiable bootstrap invariants** for the **Console runtime** in Trevron v1.2 FINAL.

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
* **Non‑repeatable**
* **Console‑only**

Failure to satisfy any invariant results in a **hard bootstrap failure**.

---

## Execution Modes (Critical Distinction)

The Console runtime operates in **two mutually exclusive modes**:

1. **Secrets Tooling Mode** (`secrets:*` commands)
2. **Runtime Console Mode** (all other commands)

This split is intentional and security‑critical.

---

## Mode A — Secrets Tooling Mode (`secrets:*`)

### Purpose

* Encrypt secrets
* Decrypt secrets
* Validate schema
* Edit plaintext safely

### Key Rule

> **Runtime secrets MUST NOT be resolved in tooling mode.**

Tooling commands operate **before encrypted secrets exist** and must never attempt to load `SecretsInterface`.

### Characteristics

| Aspect                  | Behavior                   |
|-------------------------|----------------------------|
| SecretsInterface        | ❌ NOT registered           |
| SecretsServiceProvider  | ❌ NOT invoked              |
| Secrets file            | May not exist              |
| Command dispatch        | Direct via CommandRegistry |
| Kernel                  | ❌ Not used                 |

### Required Environment

* `APP_SECRET_KEY` **must exist**
* Plaintext secrets path **must exist**
* Schema file **must exist**

### Failure Conditions

* Resolving `SecretsInterface` → **fatal**
* Missing `APP_SECRET_KEY` → **fatal**
* Invalid schema → **fatal**

---

## Mode B — Runtime Console Mode

### Purpose

* Database migrations
* Application maintenance
* Any command requiring decrypted secrets

### Key Rule

> **SecretsInterface MUST exist and MUST be validated before command execution.**

### Characteristics

| Aspect                   | Behavior               |
|--------------------------|------------------------|
| SecretsInterface         | ✅ Required             |
| SecretsServiceProvider   | ✅ Invoked              |
| Secrets file             | Must exist             |
| Command dispatch         | Kernel‑based           |
| Registry                 | Locked after COMMANDS  |

### Failure Conditions

* Missing encrypted secrets → **fatal**
* Schema mismatch → **fatal**
* Crypto extension missing → **fatal**

---

## Phase Definitions

### 1. CONFIG (Required)

**Purpose**

* Load application configuration
* Bind immutable config into container

**Guarantees after phase**

* `config` exists
* No secrets resolved
* No commands registered

**Violations**

* Missing config → fatal
* Resolution during CONFIG → fatal

---

### 2. SECRETS (Required — Context‑Sensitive)

| Mode      | Behavior                                                     |
|-----------|--------------------------------------------------------------|
| Tooling   | Phase executes, but SecretsInterface must NOT be registered  |
| Runtime   | SecretsInterface must exist and resolve                      |

**Purpose**

* Enforce cryptographic readiness
* Validate secrets schema
* Ensure immutable secrets state

**Violations**

* Secrets resolved too early → fatal
* Runtime secrets missing → fatal

---

### 3. COMMANDS (Required — FINAL PHASE)

**Purpose**

* Register all commands
* Lock the command registry

**Rules**

* Registration allowed **only before lock**
* Registry becomes immutable after phase

**Violations**

* Duplicate COMMANDS phase → fatal
* Late registration → fatal

---

## Non‑Repeatable Phase Rule

The following phase is **explicitly non‑repeatable**:

* `BootstrapPhase::COMMANDS`

Reason:

* Command registration must be deterministic
* Duplicate registration introduces ambiguity

---

## Responsibilities by Component

### `app.console.php`

* Detect execution mode
* Declare required phases
* Register kernel prerequisites
* Execute bootstrap runner

### `SecretsServiceProvider`

* **Runtime mode only**
* Registers `SecretsInterface`
* Validates schema + crypto

### `CommandPhase`

* Enforces mode‑aware invariants
* Locks registry

### `CommandRegistry`

* Accepts registrations pre‑lock
* Dispatches commands post‑lock

---

## Forbidden Actions (Hard Rules)

* Resolving secrets in tooling mode
* Skipping SECRETS in runtime mode
* Registering commands after COMMANDS
* Re‑running COMMANDS phase
* Resolving bootstrap phases from container

Each violation **must throw** `BootstrapInvariantViolationException`.

---

## Summary

* Console bootstrap is **deterministic**
* Tooling and runtime are **isolated**
* Secrets are **never optional** in runtime
* COMMANDS is **final and immutable**

This lifecycle is frozen for v1.2 FINAL.

Any change requires a version bump and architecture review.
