# Architecture Specification

Project: Trevron Framework

Document: Routing & Navigation Architecture (HTTP)

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: December 19, 2025

Owner: Mr J (Jessop Digital Systems)

© 2025 Jessop Digital Systems

---

## Purpose

This document defines the **locked architectural guarantees** of the Trevron routing system as of **v1.2 FINAL**.

Any change to behavior defined here requires:

* Contract test update(s)
* A version bump (v1.3+)
* Architecture review

---

## Scope

This document covers:

* Route definition format and compilation
* Runtime route matching
* Route attachment to Request
* Controller resolution boundary
* Separation between routing and navigation (breadcrumbs)
* Error behavior at routing boundaries

This document does **not** define UI rendering, menu styling, or application-specific navigation decisions.

---

# Architecture

## 1. Core Principle

Routing is split into **two independent products**:

1. **Routing Execution** (what code runs)
2. **Navigation Metadata** (what UI shows)

These must never influence each other at runtime.

---

## 2. Domain Concepts

### 2.1 Route

A `Route` is a runtime-executable unit defined by:

* HTTP method
* Path
* Handler definition (controller class + method)
* Optional route middleware list

A `Route` may also carry optional metadata, but **metadata is never used for matching or execution**.

---

### 2.2 RouteMetadata

`RouteMetadata` is a validated value object intended for navigation systems such as breadcrumbs and menus.

Allowed keys:

* `label` (string|null)
* `path` (string|null) — parent breadcrumb path
* `requires_token` (bool)

Unknown keys are rejected **fail-closed**.

---

### 2.3 ProcessedRoutes

`ProcessedRoutes` is the compiled routing product produced by `ProcessRoutes::process()`:

* `RouteCollection $routes` — **routing engine input**
* `NavigationMetadataCollection $metadata` — **navigation input**

This separation is a v1.2 FINAL guarantee.

---

## 3. Compilation Phase (Bootstrap Time)

### 3.1 Route Definitions (Input Contract)

Routes are defined as:

```
[
  HTTP_METHOD,
  URI,
  [
    ControllerClass::class,
    methodName,
    [optional middleware list],
    [optional metadata]
  ]
]
```

Metadata is optional and should primarily be used for GET routes (navigation).

---

### 3.2 ProcessRoutes Responsibility

`ProcessRoutes::process(array $routes): ProcessedRoutes` is the **only valid entry point** for route compilation.

It must:

* Validate base route shape (method, uri, handler)
* Normalize URI paths
* Validate `RouteMetadata` if present
* Produce a `RouteCollection` containing **only Route objects**
* Produce a `NavigationMetadataCollection` containing metadata entries
* Fail-closed with `StatusException(StatusCode::ROUTE_METADATA_INVALID)` on invalid definitions

No other component is permitted to validate route definitions.

---

### 3.3 Dispatcher Construction Boundary

FastRoute dispatcher construction is a **bootstrap responsibility**, not middleware responsibility.

The dispatcher must be built once using the compiled `RouteCollection` and then injected into routing middleware.

---

## 4. Runtime Matching Phase (Request Time)

### 4.1 ExtractRouteInfo Middleware

`ExtractRouteInfo` is the single middleware responsible for matching the request to a `Route`.

It must:

* Call the injected `FastRoute\Dispatcher`
* Interpret dispatcher results
* Attach the resolved `Route` and route parameters to the `Request`
* Delegate to the next handler

It must never:

* Build or register routes
* Consult navigation metadata
* Execute controller logic
* Use a Container

---

### 4.2 Attaching Route to Request (Locked)

On successful match (`Dispatcher::FOUND`), `ExtractRouteInfo` must:

* Set `Request::setRoute(Route $route)`
* Set `Request::setRouteParams(array $vars)`

After this point, downstream middleware may safely assume the request has a route.

---

## 5. Controller Resolution & Execution Boundary

### 5.1 ControllerResolver

`ControllerResolver` resolves controller execution targets from a `Request` with an attached `Route`.

It must:

* Read the controller handler from `Route::getHandler()`
* Resolve the controller instance via `ContainerInterface`
* Validate method existence
* Support legacy `AbstractController::setRequest()` behavior
* Return the legacy dispatch shape:

```php
/** @return array{0: callable, 1: array} */
```

`ControllerResolver` is **not** responsible for:

* Matching routes
* Running middleware stacks
* Rendering responses
* Error formatting/disclosure

---

### 5.2 Legacy Return Shape (Frozen)

The legacy return shape is frozen in v1.2 FINAL:

```php
[
  [$controller, $method],
  $vars
]
```

This is explicitly acknowledged as legacy and may be replaced only in a version bump (v1.3+).

---

## 6. Navigation Metadata (Breadcrumbs) Isolation

### 6.1 Metadata is Read-Only Navigation Input

`NavigationMetadataCollection` is intended for:

* Breadcrumb generators
* Menu builders
* Navigation trees

It must never be consulted by:

* `ExtractRouteInfo`
* FastRoute Dispatcher
* ControllerResolver
* Controller dispatch pipeline

This is a **hard guarantee**.

---

## 7. Error Policies (Routing Layer)

### 7.1 Invalid Route Definition

If route definitions are invalid at compile-time:

* `ProcessRoutes` must throw:

    * `StatusException(StatusCode::ROUTE_METADATA_INVALID)`

This is a startup-time failure (developer/configuration error).

---

### 7.2 FastRoute Infrastructure Failure

If the dispatcher throws any `Throwable` during matching:

* `ExtractRouteInfo` must throw:

    * `StatusException(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE)`

This is an infrastructure failure, not a “route not found.”

---

### 7.3 Not Found / Method Not Allowed

If the dispatcher returns:

* `NOT_FOUND` → throw `HttpException("Not Found", 404)`
* `METHOD_NOT_ALLOWED` → throw `HttpRequestMethodException(..., 405)`

These are HTTP-level classification outcomes.

---

## 8. Testing & Contract Enforcement

Routing behavior is enforced by **contract tests**.

These tests are considered part of the v1.2 FINAL product contract.

Do not relax them to “get green.”
If they fail, either:

* fix the code to restore the contract, or
* intentionally bump to v1.3 and update the contract tests.

---

## 9. Traceability Matrix (v1.2 FINAL)

| Section | Responsibility               | Canonical Component                              |
|--------:|------------------------------|--------------------------------------------------|
|      §3 | Compile routes + metadata    | `ProcessRoutes`, `ProcessedRoutes`               |
|    §3.3 | Build dispatcher once        | App bootstrap (integration boundary)             |
|      §4 | Match request to route       | `ExtractRouteInfo`                               |
|    §4.2 | Attach route + params        | `Request::setRoute`, `Request::setRouteParams`   |
|      §5 | Resolve controller callable  | `ControllerResolver`                             |
|      §6 | Navigation read model        | `NavigationMetadataCollection`                   |
|      §7 | Routing error policy         | `StatusCode`, `StatusException`, HTTP exceptions |

No component may assume responsibilities assigned to another section.

---

## 10. Version Freeze Statement

Version **v1.2 FINAL** represents a frozen routing baseline.

No new concepts may be introduced without incrementing the MINOR or MAJOR version number.

All implementation work must conform to this specification unless explicitly superseded by a later version.

---
