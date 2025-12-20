# Routing Architecture Specification

Project: Trevron Framework
Document: Routing System Architecture
**Version: v1.2 FINAL**
Status: **ARCHITECTURALLY FROZEN**
Effective Date: December 19, 2025
Owner: Mr J (Jessop Digital Systems)
© 2025 Jessop Digital Systems

---

## Purpose

This document defines the **locked architectural guarantees** of the Trevron routing system as of **v1.2 FINAL**.

Changes to any behavior defined here require:

* Contract test updates
* A version bump (v1.3+)
* Architecture review

---

## Scope

This document covers:

* Route definition shape and processing
* Route matching and request augmentation
* Middleware and dispatch boundaries
* Route metadata (navigation/breadcrumbs) separation
* Error normalization responsibilities in routing components

This document does **not** cover:

* Controller construction/DI rules (ControllerDispatcher architecture)
* Error rendering (ErrorResponder architecture)
* UI layout / styling / view concerns

---

# Architecture

## Routing System v1.2 FINAL — Status

**ARCHITECTURALLY FROZEN** as of **v1.2 FINAL**.

Any change requires:

* Contract test update(s)
* Version bump (v1.3+)
* Architecture review

---

## Core Principle

Routing produces **intent**, not **execution**.

* Routing identifies **what endpoint** is targeted (`Route`)
* Routing attaches route + params to the `Request`
* Execution happens **after routing** via `RouteDispatcher → ControllerDispatcher`
* Navigation/breadcrumb concerns are **data-only** and must not influence routing decisions

---

## Responsibility Boundaries

### Routing Responsibilities

The routing system is responsible for:

* Validating route definitions
* Normalizing route paths
* Producing `Route` objects
* Matching requests to routes via a dispatcher/matcher
* Attaching:

    * `Route` to `Request`
    * route parameters to `Request`
* Providing route-level middleware lists for downstream middleware resolution
* Producing navigation metadata (breadcrumbs/menu hints) as a separate output

Routing must **not**:

* Instantiate controllers
* Invoke controller callables
* Call container services for controllers
* Render HTML/JSON/CLI output
* Perform error disclosure decisions
* Decide between HTML vs JSON response formats

---

### Execution Responsibilities (Out of Scope but enforced by routing boundaries)

Execution is responsible for:

* Resolving controller instances
* Validating method existence
* Invoking controllers and returning `Response`
* Throwing `StatusException` for known failures

Routing must remain ignorant of those rules.

---

## Data Model

### Route Definition (Authoritative Input)

A route definition is expressed as:

```
[HTTP_METHOD, URI, [ControllerClass, methodName, middlewareList?, metadata?]]
```

Rules:

* `HTTP_METHOD` is a string (e.g., `"GET"`, `"POST"`)
* `URI` is a normalized path (e.g., `"/"`, `"/users"`)
* Controller descriptor is:

    * `ControllerClass` (class-string)
    * `methodName` (string)
    * `middlewareList` optional (array of class-string middleware)
    * `metadata` optional (array) used for navigation only

---

### Route (Canonical Runtime Object)

A `Route` is the canonical runtime object and must be immutable in behavior:

* method
* path
* handler descriptor `[ControllerClass, methodName, middleware?, metadataObject?]`
* middleware list accessible via `RouteMiddlewareAwareInterface`

The router/matcher must not mutate route state once created.

---

### Route Metadata (Navigation-only)

Route metadata is optional and is used strictly for navigation concerns such as breadcrumbs and menus.

Canonical keys (and only these keys):

* `label` (string|null)
* `path` (string|null) — parent route path for breadcrumb chaining
* `requires_token` (bool) — navigation/security hint (not routing logic)

Metadata must be validated by a single canonical validator (see below).
Unknown keys are a **hard failure**.

---

## Processing Pipeline (v1.2 FINAL)

All routing definitions must flow through the same pipeline:

```
Raw Route Definitions
    ↓
ProcessRoutes (validation + normalization)
    ↓
ProcessedRoutes (split outputs)
    ├── RouteCollection (routing-only)
    └── NavigationMetadataCollection (navigation-only)
```

No consumer may receive raw route definitions directly.

---

## ProcessRoutes (Canonical Gate)

`ProcessRoutes` is the canonical gate responsible for:

* Validating route definition structure
* Normalizing URIs
* Validating metadata via `RouteMetadata::fromArray()`
* Producing **two** outputs:

    * `RouteCollection` — used by routing engine
    * `NavigationMetadataCollection` — used by navigation/breadcrumb generator

### Fail Policy

If route structure or metadata is invalid:

* Throw `StatusException(StatusCode::ROUTE_METADATA_INVALID, ...)`

This is an architectural guarantee.

---

## Hard Separation Rule (Routes vs Metadata)

The routing engine must not access navigation metadata.

This must be enforced structurally:

* `ExtractRouteInfo` (routing middleware) receives `RouteCollection` only
* `BreadcrumbGenerator` receives `NavigationMetadataCollection` only

Passing a combined associative array (e.g. `['routes' => ..., 'metadata' => ...]`) into both consumers is considered an architectural violation.

---

## Matching & Request Augmentation

### ExtractRouteInfo Middleware (Routing Augmentation)

`ExtractRouteInfo` is the canonical middleware responsible for:

* Matching a request `(method, path)` to a `Route`
* Attaching the matched route to the request
* Attaching route params to the request
* Delegating to the next middleware

Rules:

* It must not render output
* It must not invoke controllers
* It must not create controllers or call containers
* It may throw exceptions representing match results:

    * Not found → a 404-class exception (later normalized to StatusCode by Kernel/Error layer)
    * Method not allowed → 405-class exception

### Infrastructure Rule (v1.2 FINAL)

Dispatcher infrastructure (FastRoute dispatcher) must be built once and reused where possible.
Middleware should not rebuild routing infrastructure per request, except in development-only prototypes.

(If you keep building it inside middleware right now, that is allowed temporarily, but should be marked “implementation detail” and targeted for v1.3 refinement.)

---

## Dispatch Boundary (Routing → Execution)

### RouteDispatcher (Execution Boundary)

Routing ends at the `RouteDispatcher`.

`RouteDispatcher` is responsible for:

* Calling `ControllerDispatcherInterface::dispatch($request)`
* Converting unexpected `Throwable` into:

    * `StatusException(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE, ...)`
* Re-throwing `StatusException` unchanged

Routing must not bypass this boundary.

---

## Middleware Responsibilities

### Route-level Middleware

Routes may declare middleware as part of the route definition:

* Middleware is an ordered list of middleware class-strings
* Middleware resolution/instantiation is handled by a separate subsystem (resolver/container), not by routing itself
* Routing only **exposes** middleware list via `Route::getMiddleware()`

### Routing Middleware (System-level)

Routing middleware includes:

* `ExtractRouteInfo` (match + attach route/params)

Routing middleware must be executed before any controller dispatch.

---

## Breadcrumb Architecture (Navigation Consumer)

Breadcrumb generation is a navigation concern.

Breadcrumb generation may:

* Consume `NavigationMetadataCollection`
* Use:

    * `uri` (current route URI)
    * `label` (breadcrumb label)
    * `path` (parent route URI)
* Build a breadcrumb chain by following parent links until `path === null`

Breadcrumb generation must not:

* Read routing engine internals
* Dispatch routes
* Match routes
* Inspect controller classes
* Read DI container

Breadcrumb generation must be deterministic and testable.

---

## Error Handling Guarantees in Routing (v1.2 FINAL)

### Invalid Route Definitions / Metadata

* Must throw `StatusException(StatusCode::ROUTE_METADATA_INVALID, ...)`

### Controller Dispatch Failures

* Must be wrapped by `RouteDispatcher` into:

    * `StatusException(StatusCode::HTTP_ROUTE_DISPATCH_FAILURE, ...)`

### Not Found / Method Not Allowed

* May throw HTTP-level exceptions (404/405) during routing middleware
* Final normalization into `ErrorContext` is handled by the Kernel/Error system

Routing must never render errors.

---

## Compliance Checklist (Locked)

### Routing Separation

✅ Routing produces intent, not execution
✅ Routing engine consumes `RouteCollection` only
✅ Navigation consumes `NavigationMetadataCollection` only
✅ Metadata never influences route matching

### Routing Middleware

✅ `ExtractRouteInfo` attaches `Route` to `Request`
✅ Route params attached to `Request`
✅ No container/controller invocation in routing middleware

### Dispatch Boundary

✅ Controller execution only occurs through `RouteDispatcher → ControllerDispatcher`
✅ `RouteDispatcher` wraps unexpected `Throwable` into `HTTP_ROUTE_DISPATCH_FAILURE`
✅ `StatusException` is rethrown untouched

### Error Output

✅ Routing never renders HTML/JSON/CLI
✅ Routing never decides disclosure
✅ Routing never selects output format

---

## Contract Tests (v1.2 FINAL)

The following contract tests are mandatory and enforce this architecture:

* Route processing produces split outputs:

    * `ProcessRoutes` returns `RouteCollection` + `NavigationMetadataCollection`
* Routing middleware attaches route and params:

    * `ExtractRouteInfo` sets `Request->route` and `Request->routeParams`
* Dispatch boundary wraps unexpected throwables:

    * `RouteDispatcher` converts `Throwable` → `HTTP_ROUTE_DISPATCH_FAILURE`
* Route metadata validation fails closed:

    * Unknown metadata keys → `ROUTE_METADATA_INVALID`

Contract tests must not be weakened to “get green.”
If they fail:

* fix the code to restore the contract, or
* bump to v1.3 and update the contract tests intentionally.

---

## Version Freeze Statement

Version **v1.2 FINAL** represents a frozen architectural baseline for routing.

No new responsibilities may be introduced to routing components without:

* a version bump (v1.3+)
* contract test updates
* explicit architectural review

---

If you want, next we’ll do exactly what you did for Kernel:

1. Create the **Routing v1.2 contract test file** (ProcessRoutes split, ExtractRouteInfo attach, RouteDispatcher wrapping)
2. Then freeze it ruthlessly.
