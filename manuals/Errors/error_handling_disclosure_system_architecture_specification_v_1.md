# Jessop Digital Systems

## Error Handling & Disclosure System

**Copyright (c) 2025**
December 15, 2025

### Designed By
Mr

---

# Error Handling & Disclosure System
## Architecture Specification

**Version:** 1.0

**Status:** Active (Living Document)

**Audience:** Framework architects, core contributors, senior developers

**Purpose:** This document defines the canonical architecture for error handling and error disclosure within the framework. It formalizes goals, constraints, responsibilities, and extension points. All future error-related changes must align with this specification.

---

## 1. Scope & Intent

This specification governs how the framework:

- Classifies failures
- Controls disclosure of sensitive information
- Renders errors across multiple formats (HTML, JSON, CLI)
- Separates development diagnostics from production safety

This document does **not** describe UI styling, logging backends, or third-party integrations, except where they affect disclosure guarantees.

---

## 2. Design Principles

### 2.1 Fail Closed by Default

If any part of the error system is misconfigured, incomplete, or unavailable, the framework must default to **non-disclosure** of sensitive information.

### 2.2 Capability-Based Disclosure

Error disclosure is treated as an explicit capability that must be intentionally granted. It is **not** inferred from environment names, hostnames, or runtime heuristics.

### 2.3 Separation of Responsibilities

No single layer is allowed to:
- Both decide *what is safe* and *how it is rendered*
- Inspect raw exceptions inside presentation logic

Each layer has a single, enforceable responsibility.

### 2.4 Audience Awareness

Errors may be rendered for:
- Humans (HTML)
- Machines (JSON)
- Developers (CLI)

The same underlying error context may be rendered differently depending on the audience.

---

## 3. Failure Pipeline Overview

All failures flow through the same conceptual pipeline:

```
Throwable
   ↓
Error Classification
   ↓
ErrorContext
   ↓
Disclosure Policy
   ↓
Sanitized ErrorContext
   ↓
Renderer (HTML / JSON / CLI)
```

No stage may be skipped or short-circuited.

---

## 4. Error Classification

### 4.1 Purpose

Error classification defines *what went wrong* in a framework-stable, machine-readable way.

### 4.2 Characteristics

- Independent of HTTP transport
- Independent of presentation
- Stable across framework versions

### 4.3 Common Dimensions

- **HTTP Status** (e.g. 404, 500)
- **Internal Status Code** (e.g. ROUTE_NOT_FOUND)
- **Status Category** (e.g. CLIENT, SERVER, SECURITY)

Classification **never implies disclosure**.

---

## 5. ErrorContext

### 5.1 Purpose

`ErrorContext` is the single source of truth for all error-related data once a failure has been classified.

### 5.2 Required Properties

- HTTP status code
- Internal status code
- Status category
- Public-safe message

### 5.3 Optional Properties

- Throwable (exception)
- Debug metadata (stack traces, context data)

### 5.4 Safety Rule

`ErrorContext` **may contain sensitive data**, but must assume that data will be removed unless explicitly permitted.

---

## 6. Disclosure Policy

### 6.1 Purpose

The disclosure policy determines whether sensitive error details may be exposed beyond internal boundaries.

### 6.2 Core Rules

- Disclosure is explicit
- Disclosure is revocable
- Disclosure is independent of `.env` mode flags

### 6.3 Policy Types

- **Production Policy**: Never allows sensitive disclosure
- **Development Policy**: Allows disclosure only when explicitly authorized

### 6.4 Authorization Model

Authorization must:
- Be secret-based
- Be non-user-controllable
- Be injectable at runtime

---

## 7. Error Sanitization (Safety Gate)

### 7.1 Purpose

The sanitizer enforces disclosure decisions and acts as the final safety gate before rendering.

### 7.2 Behavior

When disclosure is **not** allowed:
- Exceptions are removed
- Debug metadata is stripped
- Only public-safe data remains

### 7.3 Guarantee

No renderer may receive sensitive data unless the sanitizer explicitly allows it.

---

## 8. Rendering Strategy

### 8.1 HTML Rendering

- Human-readable
- Friendly language
- Shared error layouts
- Optional debug sections (development only)

### 8.2 JSON Rendering

- Structured and predictable
- Machine-readable contracts
- Internal codes allowed
- Debug data conditionally included

### 8.3 CLI Rendering

- Always verbose
- Intended for trusted developer use
- Not subject to HTTP disclosure rules

---

## 9. Kernel Responsibilities

The Kernel is responsible for:

- Catching unhandled throwables
- Mapping exceptions to error classifications
- Creating ErrorContext instances
- Invoking the disclosure pipeline
- Selecting the appropriate renderer

The Kernel must not render errors directly.

---

## 10. Explicit Non-Goals

The following are intentionally excluded from this architecture:

- UI theming or branding
- Automatic disclosure based on host or IP
- Error handling logic inside templates
- Direct exception rendering

---

## 11. Testing Requirements

The following behaviors must be covered by automated tests:

- Sensitive data is never exposed without authorization
- Disclosure fails closed when misconfigured
- HTML and JSON outputs remain stable
- Sanitization always removes exceptions when required

---

## 12. Versioning & Evolution

### 12.1 Versioning Policy

- MAJOR: Breaking architectural changes
- MINOR: New capabilities without breaking guarantees
- PATCH: Internal refinements

### 12.2 Change Control

All changes must:
- Preserve fail-closed behavior
- Respect separation of concerns
- Be documented in a version increment

---

## 13. Future Extensions (Non-Binding)

Potential future additions include:

- Exception-to-status mapping tables
- Central ErrorResponder abstraction
- Structured logging integration
- External error reporting hooks
- Security audit trails

---

## 14. Canonical Statement

This specification defines the authoritative error handling architecture for the framework. Any implementation or extension that violates the principles or guarantees described herein is considered non-compliant.

