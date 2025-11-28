Perfect — thanks for the clarification.
Below is **Part 1: The Full User Manual** for the JSON Package.
Once you review it, I’ll generate **Part 2: The complete Pest test suite** (Unit + Feature) fully aligned to your PSR-4 structure.

---

# 📘 **JSON Package User Manual**

### *For the JDS Framework*

---

# 1. Introduction

The JDS JSON Package provides a clean, SRP-driven system for:

* **Encoding and writing structured JSON** to disk
* **Reading, sorting, and decoding JSON files**
* Ensuring **safe file operations**, **predictable directory handling**, and **extensible components**
* Providing a framework-level **service provider registration** system for seamless integration

This system is designed for internal use within the JDS Framework. It can also be consumed by applications *built on top of* the framework through the bootstrap configuration.

---

# 2. Package Structure

```
src/
    FileSystem/
        FilePathValidator.php
        FileNameGenerator.php
        FileLister.php
        JsonFileWriter.php
        JsonFileReader.php

    Json/
        JsonBuilder.php
        JsonEncoder.php
        JsonDecoder.php
        JsonSorter.php
        JsonLoader.php

    ServiceProvider/
        JsonServiceProvider.php
        JsonReaderServiceProvider.php

tests/
    Unit/
    Feature/
```

All classes follow PSR-4 autoloading standards under the root namespace:

```
JDS\
```

---

# 3. Overview of Responsibilities

## 3.1 JSON Writing Components

| Component               | Responsibility                                                                      |
| ----------------------- | ----------------------------------------------------------------------------------- |
| **JsonEncoder**         | Converts PHP values into JSON (with consistent error handling).                     |
| **FilePathValidator**   | Normalizes paths, ensures directories exist, checks writeability.                   |
| **FileNameGenerator**   | Creates timestamped filenames and ensures uniqueness.                               |
| **JsonFileWriter**      | Writes JSON strings to disk safely.                                                 |
| **JsonBuilder**         | High-level class orchestrating encoding → filename creation → validation → writing. |
| **JsonServiceProvider** | Registers all writing-related services into the container.                          |

---

## 3.2 JSON Reading Components

| Component                     | Responsibility                                             |
| ----------------------------- | ---------------------------------------------------------- |
| **FileLister**                | Retrieves file lists using glob (no sorting).              |
| **JsonSorter**                | Sorts file paths (oldest, newest, etc.).                   |
| **JsonFileReader**            | Safely loads JSON from disk.                               |
| **JsonDecoder**               | Decodes JSON into objects *or* associative arrays.         |
| **JsonLoader**                | High-level orchestrator: list → sort → read → decode.      |
| **JsonReaderServiceProvider** | Registers all reading-related services into the container. |

---

# 4. Service Providers

Both providers follow the same pattern as your other framework components.

## 4.1 JsonServiceProvider (Encoding/Writing)

Registers:

* JsonEncoder
* JsonBuilder
* FileNameGenerator
* FilePathValidator
* JsonFileWriter
* FileDeleter (if used)

Usage (framework bootstrap):

```php
$container->addServiceProvider(JDS\ServiceProvider\JsonServiceProvider::class);
```

---

## 4.2 JsonReaderServiceProvider (Decoding/Reading)

Registers:

* JsonDecoder
* JsonFileReader
* FileLister
* JsonSorter
* JsonLoader
* FilePathValidator (reused)

Usage (framework bootstrap):

```php
$container->addServiceProvider(JDS\ServiceProvider\JsonReaderServiceProvider::class);
```

---

# 5. Adding Providers in Application Bootstrap

You said you’d supply your bootstrap code — but here is the expected minimum:

```php
$container->defaultToShared(true);

$container->addServiceProvider(JDS\ServiceProvider\JsonServiceProvider::class);
$container->addServiceProvider(JDS\ServiceProvider\JsonReaderServiceProvider::class);
```

This makes both the reading and writing components available anywhere in the application.

---

# 6. Using the JSON Writing System

### Example: Saving Data as JSON

```php
$jsonBuilder = $container->get(JDS\Json\JsonBuilder::class);

$result = $jsonBuilder->save(
    data: ['name' => 'John', 'role' => 'Admin'],
    directory: '/var/storage/users',
    baseName: 'user'
);

if (!$result['success']) {
    // handle error
}
```

**Result:**

* File path validated
* Filename auto-generated with timestamp
* JSON encoded
* JSON written to disk

---

# 7. Using the JSON Reading System

### Example: Loading All JSON Files (oldest → newest)

```php
$loader = $container->get(JDS\Json\JsonLoader::class);

$response = $loader->loadAll('/var/storage/users', assoc: false);

if ($response['success']) {
    $objects = $response['data']; // array of decoded objects
}
```

You may pass:

```php
assoc: false  // return objects
assoc: true   // return associative arrays
```

Sorting is abstracted — `JsonLoader` uses the sorter behind the scenes.

---

# 8. Custom Sorting

You may sort the files manually:

```php
$lister = $container->get(JDS\FileSystem\FileLister::class);
$sorter = $container->get(JDS\Json\JsonSorter::class);

$files = $lister->list('/var/storage/users')['files'];

$sorted = $sorter->sortNewest($files);
```

---

# 9. Error Handling

Errors are always returned in the form:

```php
[
    'success' => false,
    'error' => 'Description here'
]
```

Never exceptions (except in extreme cases).
This keeps the system predictable for framework users.

---

# 10. Extending the System

You may replace any component by extending the container:

### Example: Custom Sorter

```php
$container->extend(JsonSorter::class)
    ->setConcrete(CustomJsonSorter::class);
```

### Example: Replace decoder behavior

```php
$container->extend(JsonDecoder::class)
    ->setConcrete(MyDecoder::class);
```

Because everything follows SRP, swapping classes never breaks adjacent components.

---

# 11. Best Practices

* Always validate paths using the provided FilePathValidator
* Always use JsonBuilder rather than manual encoding
* Always use JsonLoader for reading collections of files
* Avoid accessing JSON files directly — the package ensures safe operations
* Never bypass the container for instantiation

---

# 12. Glossary

| Term             | Meaning                                                                     |
| ---------------- | --------------------------------------------------------------------------- |
| SRP              | Single Responsibility Principle — each class has only one reason to change. |
| JSON Builder     | High-level writing orchestrator.                                            |
| JSON Loader      | High-level reading orchestrator.                                            |
| Service Provider | Registers dependencies into the container.                                  |
| Container        | Dependency injection system used throughout the framework.                  |

---

