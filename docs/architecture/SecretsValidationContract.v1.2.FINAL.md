# Architecture Specification

Project: Trevron Framework

Document: Secrets Validation Contract

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: December 29, 2025

Owner: Mr J (Jessop Digital Systems)

© 2025 Jessop Digital Systems

## Overview

This document defines the **validation contract** for plaintext secrets within the Trevron Framework v1.2 FINAL. It describes **what is validated**, **how validation works**, **what is explicitly not supported**, and **where this validation fits in the bootstrap and secrets lifecycle**.

This contract is **security‑critical**. Any changes require an architecture review and a version bump.

---

## 1. Purpose of Secrets Validation

Secrets validation exists to ensure that **all required secrets are present and correctly structured** *before* encryption and runtime locking occur.

Validation answers one question only:

> *Does the provided plaintext secrets file contain every required key, in the correct nested structure, that the application depends on?*

It intentionally does **not** attempt to validate values, types, formats, or cryptographic correctness.

---

## 2. What Secrets Validation Enforces

Secrets validation enforces the following guarantees:

* All **required top‑level keys** exist
* All **required nested keys** exist
* The **hierarchical structure** of secrets matches expectations
* Missing secrets cause **hard failure**
* Validation failures are **explicit and deterministic**

If validation passes, the secrets file is considered *structurally complete*.

---

## 3. What Secrets Validation Does NOT Enforce

Secrets validation **does not**:

* Validate data types (string, int, boolean, etc.)
* Validate formats (URLs, email addresses, token shapes)
* Perform JSON Schema validation
* Apply defaults or fallbacks
* Modify or normalize secrets
* Read encrypted secrets
* Perform encryption or decryption

These omissions are **intentional** and are part of the security design.

---

## 4. Structural Schema Model

Trevron uses a **structural schema**, not JSON Schema.

The schema **mirrors the required secrets structure exactly**.

### Example

**Required secrets structure:**

```json
{
  "db": {
    "user": "root",
    "password": "secret"
  },
  "jwt": {
    "access": "token"
  }
}
```

**Corresponding schema:**

```json
{
  "db": {
    "user": {},
    "password": {}
  },
  "jwt": {
    "access": {}
  }
}
```

Each key in the schema is treated as **required**.

---

## 5. Validation Algorithm

Validation is performed recursively using the following rules:

1. Every key in the schema **must exist** in the secrets file
2. If a schema value is an object, the corresponding secrets value **must also be an object**
3. Validation descends recursively through the structure
4. The first violation causes validation to fail immediately

There is **no partial success** and **no recovery logic**.

---

## 6. Failure Behavior

On validation failure:

* Validation stops immediately
* A clear error message is emitted
* The CLI command exits with a **non‑zero status code**
* Encryption and runtime locking **must not proceed**

Example error:

```
Validation failed: Missing required secret: db.password
```

---

## 7. ValidateSecretsCommand

The `secrets:validate` command implements this contract.

### Responsibilities

* Load plaintext secrets file
* Load structural schema file
* Validate structure using `SecretsValidator`
* Report success or failure

### Explicit Non‑Responsibilities

* Encryption
* Decryption
* Runtime secrets access
* Secret value inspection

---

## 8. Relationship to Other Secrets Commands

| Command             | Role                              |
|---------------------|-----------------------------------|
| `secrets:edit`      | Edit plaintext secrets            |
| `secrets:validate`  | Validate structural completeness  |
| `secrets:encrypt`   | Encrypt validated secrets         |
| `secrets:decrypt`   | Inspect encrypted secrets         |

Validation **must occur before encryption** in any secure workflow.

---

## 9. Relationship to Bootstrap Lifecycle

Secrets validation occurs **before** the `SECRETS` bootstrap phase.

After validation and encryption:

* Secrets are loaded into memory
* Secrets are **locked** during `SecretsPhase`
* Runtime access becomes **read‑only**

Validation is never performed during runtime.

---

## 10. Security Rationale

This design intentionally favors:

* Determinism over convenience
* Explicit failure over silent recovery
* Structural guarantees over semantic guessing
* Minimal attack surface

Secrets validation exists to **fail early and loudly**.

---

## 11. Stability Guarantees

This contract is considered **stable** for Trevron v1.2 FINAL.

Changes require:

* Architecture review
* Test updates
* Documentation revision
* Version bump

---

## 12. Summary

Secrets validation in Trevron:

* Enforces structure, not semantics
* Uses a mirrored schema model
* Fails hard on missing requirements
* Is CLI‑driven and deterministic
* Is foundational to secrets security

This contract is a **core security invariant** of the framework.

