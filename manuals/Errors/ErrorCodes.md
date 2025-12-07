# Jessop Digital Systems
## JDS Error Code System
**Copyright (c) 2025**
December 7, 2025

JDS uses a structured, enum-based error code system to provide consistent,
human-readable, and machine-parseable error information across the entire
framework (HTTP, Console, Database, Logging, etc.).

### 1. Core Types

#### `StatusCategory` (enum)

Represents a broad error category. Each category is assigned a numeric range:

- `Server` (500–599)
- `Containers` (2100–2199)
- `Controllers` (2200–2299)
- `Authentication` (2300–2399)
- `Entity` (2400–2499)
- `Form` (2700–2799)
- `Logging` (2800–2899)
- `Console` (3500–3599)
- `ConsoleKernel` (3600–3799)
- `Http` (3800–3899)
- `HttpKernel` (3900–4099)
- `Database` (4100–4199)
- `Mail` (4200–4299)
- `FileSystem` (4300–4399)
- `Json` (4400–4499)
- `Image` (4500–4599)
- …and so on.

You can resolve a category for any integer code:

```php
$category = StatusCategory::fromCode(4103); // Database
````

#### `StatusCode` (enum)

Represents a specific error condition within a category.

Each case:

* Has an integer value (the error code)
* Knows its own `StatusCategory`
* Provides a default, human-readable message
* Provides a formatted form like `"[4100] Database Error: Migration apply failed"`

Usage:

```php
use JDS\Error\StatusCode;

$code = StatusCode::DATABASE_MIGRATION_APPLY_FAILED;

$code->value;          // 4100
$code->category();     // StatusCategory::Database
$code->defaultMessage(); // "Database Error: Migration apply failed"
$code->formatted();    // "[4100] Database Error: Migration apply failed"
```

#### `StatusException`

A base exception type that wraps a `StatusCode`:

```php
use JDS\Error\StatusCode;
use JDS\Error\StatusException;

throw new StatusException(StatusCode::DATABASE_GENERAL_ERROR);
```

This will:

* Use the default message from the status code, unless you override it
* Set `getCode()` to the numeric status code
* Allow downstream consumers to inspect the code/category

### 2. Logging and Error Processing

#### `ExceptionLogger`

Logs exceptions using:

* `StatusCode` (for code + default message)
* Optional details string
* Optional exception context (stack trace in non-production)

```php
$logger->log(
    StatusCode::DATABASE_MIGRATION_APPLY_FAILED,
    'Failed while migrating users table.',
    'error',
    $e
);
```

This produces log output like:

```text
[4100] Database Error: Migration apply failed | Details: Failed while migrating users table.
```

Monolog handles the stack trace formatting via the logged exception context.

#### `ErrorProcessor`

Central orchestrator for error logging and rendering:

```php
ErrorProcessor::initialize($exceptionLogger);

try {
    // risky operation
} catch (Throwable $e) {
    ErrorProcessor::process(
        $e,
        StatusCode::DATABASE_GENERAL_ERROR,
        'Database operation failed.'
    );
}
```

`ErrorProcessor` will:

1. Log the error via `ExceptionLogger`
2. Delegate to `ExceptionHandler::render()` to present it (CLI/HTTP/etc.)
3. Throw a `StatusException` if it is used before initialization

### 3. HTTP Kernel Integration

The HTTP `Kernel` uses `StatusCode` and `ErrorProcessor` to capture unexpected
exceptions in the request pipeline and convert them to HTTP responses while
ensuring all details are logged consistently.

```php
catch (Throwable $e) {
    ErrorProcessor::process(
        $e,
        StatusCode::HTTP_KERNEL_GENERAL_FAILURE,
        'An unexpected error occurred while processing the request.'
    );

    $response = $this->createExceptionResponse($e);
}
```

---

Exception Trace Formatting

JDS includes a custom ExceptionFormatter designed to produce readable stack traces during development. This formatter is never used in logs and never displayed in production.

Where traces appear

| Layer                        | Displays trace? | Notes                                     |
|------------------------------|-----------------|-------------------------------------------|
| ExceptionLogger              | ❌ No            | Only logs structured exception context    |
| ErrorProcessor               | ❌ No            | Delegates to ExceptionHandler             |
| HTTP/Console Kernel          | ❌ No            | Only forwards exception                   |
| ExceptionHandler (dev mode)  | ✅ Yes           | Uses ExceptionFormatter for clean output  |
| ExceptionHandler (prod mode) | ❌ No            | Hides trace for security                  |


Why this design?

Prevents leaking sensitive details in logs or production

Keeps logs machine-readable for monitoring tools

Gives developers clean, readable stack traces

Matches industry best practices (Symfony, Laravel, Slim, ExpressJS, etc.)

---

This gives you a **fully typed, enum-driven, centralized error system** that your entire framework can lean on for years without turning into a mess.


