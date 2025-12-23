# Architecture Specification

Project: Trevron Framework

Document: Routing & Navigation Architecture (HTTP)

**Version: v1.2 FINAL**

Status: **ARCHITECTURALLY FROZEN**

Effective Date: December 23, 2025

Owner: Mr J (Jessop Digital Systems)

© 2025 Jessop Digital Systems

## Overview

This document defines the **official bootstrap lifecycle** of the Trevron Framework.
All application bootstrapping MUST conform to this architecture.

Bootstrap logic is **explicit**, **ordered**, and **contract-driven**.

---

## Bootstrap Phases

### Phase 0 – Environment Resolution (Application-Owned)

**Responsibilities**
- Resolve filesystem paths
- Load environment variables
- Decide runtime mode

**Rules**
- No container access
- No framework services

**Files**
- `public/index.php`
- `bootstrap/env.php`

---

### Phase 1 – Configuration & Route Assembly (Application-Owned)

**Responsibilities**
- Load configuration files
- Load and process routes

**Rules**
- No container mutation
- Routes processed exactly once

**Files**
- `bootstrap/ConfigBootstrap.php`
- `routes/web.php`

**Outputs**
- `array $config`
- `ProcessedRoutes $routes`

---

### Phase 2 – Container Construction (Framework-Owned)

**Responsibilities**
- Instantiate container
- Register immutable services
- Register configuration object

**Rules**
- No filesystem access
- No environment access

**Files**
- `bootstrap/ContainerBootstrap.php`
- `services/*.php`

---

### Phase 3 – Infrastructure Bootstrap (Framework-Owned)

**Responsibilities**
- Convert domain objects into infrastructure
- Wire systems requiring pre-built state

**Rules**
- No discovery
- No config loading
- Dependencies injected explicitly

**Files**
- `JDS\Bootstrap\*Bootstrap`

---

### Phase 4 – Runtime Execution (Framework-Owned)

**Responsibilities**
- Execute kernels
- Handle requests
- Dispatch events

**Files**
- `Kernel`
- `public/index.php`

---

## Bootstrap Contracts

All infrastructure bootstrap phases MUST implement:

```php
JDS\Contracts\Bootstrap\BootstrapPhaseInterface
