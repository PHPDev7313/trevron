# Architecture Specification

Project: Trevron Framework

Document: HTTP Kernel & Error Handling Architecture

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: December 19, 2025

Owner: Mr J (Jessop Digital Systems)

© 2025 Jessop Digital Systems

---

## Purpose

This document defines the **locked architectural guarantees** of the Trevron
framework as of v1.2 FINAL.

Changes to any behavior defined here require:
- Contract test updates
- A version bump (v1.3+)
- Architecture review

---

## Scope

This document covers:
- HTTP Kernel responsibilities
- Error normalization and delegation
- StatusCode vs HTTP status semantics
- Lifecycle event boundaries

---

# Architecture

## Kernel v1.2 FINAL — Compliance Checklist (Locked)

### Status
**ARCHITECTURALLY FROZEN** as of **v1.2 FINAL**.

Any behavior change requires:
- Contract test update(s)
- Version bump (v1.3+)
- Architecture review

---

## Kernel Responsibilities

The HTTP Kernel is a traffic cop:

- Builds the middleware pipeline
- Dispatches controller execution via RouteDispatcher
- Captures framework exceptions
- Normalizes all errors into `ErrorContext`
- Delegates error responses to `ErrorResponder`
- Dispatches lifecycle events (`ResponseEvent`, `TerminateEvent`)
- Never renders templates, JSON, or output

---

### Controller Dispatch Failure Policy (v1.2 FINAL)

- Any Throwable thrown during controller dispatch
  is wrapped by RouteDispatcher into:

  StatusCode::HTTP_ROUTE_DISPATCH_FAILURE (3801)

- The Kernel treats this as a StatusException
  and delegates to ErrorResponder unchanged

- The Kernel's generic Throwable catch only applies
  to failures occurring outside RouteDispatcher

This behavior is enforced by contract tests.

---

### HTTP Status vs Framework Status (v1.2 FINAL)

- `Response::getStatusCode()` represents the HTTP transport status
- `ErrorContext::$httpStatus` represents the framework error code

They MAY differ.

Example:
- HTTP response status: `500`
- Framework status code: `3801 (HTTP_ROUTE_DISPATCH_FAILURE)`

This separation is intentional and enforced by contract tests.

---

## Pre-Test Kernel Compliance Checklist

✅ Kernel constructor injects `ErrorResponder`  
✅ Kernel injects `EventDispatcher` **only for lifecycle events** (ResponseEvent/TerminateEvent)  
✅ Kernel contains **no** error controllers, fallbacks, or “special routes”  
✅ Kernel contains **no** `createExceptionResponse()` logic  
✅ Kernel catch blocks only build `ErrorContext` (no rendering, no branching)  
✅ Kernel always delegates to `ErrorResponder`  
✅ Kernel returns the responder’s `Response` verbatim

If all are checked → you may write/maintain Kernel tests.

---

## Contract Tests

Kernel behavior is enforced by ruthless contract tests:

- `tests/Contract/Http/KernelV12ContractTest.php`

These tests are considered part of the v1.2 FINAL product contract.
Do not relax them to “get green.”
If they fail, either:
- fix the code to restore the contract, or
- intentionally bump to v1.3 and update the contract tests.
