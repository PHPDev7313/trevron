<?php

namespace JDS\Http;

use JDS\Exceptions\Http\InvalidArgumentException;

final class StatusCodeManager
{
    /**
     * Base codes for each category (100-range blocks).
     * ConsoleKernel now expanded to 200-range
     * HttpKernel now expanded to 200-range
     *
     * Base numeric ranges assigned to each status code category.
     *
     *  Each category receives (by design) a 100-code block EXCEPT
     *  ConsoleKernel, and HttpKernel, which both receive a 200-code block.
     *
     *  NOTE: Update these ONLY if your category taxonomy changes.
     */
    private const CATEGORY_BASE = [
        'Server'          => 500,  //  500 - 599
        'Containers'      => 2100, // 2100 - 2199
        'Controllers'     => 2200, // 2200 - 2299
        'Authentication'  => 2300, // 2300 - 2399
        'Entity'          => 2400, // 2400 - 2499
        'Enum'            => 2500, // 2500 - 2599
        'EventListener'   => 2600, // 2600 - 2699
        'Form'            => 2700, // 2700 - 2700
        'Logging'         => 2800, // 2800 - 2899
        'Middleware'      => 2900, // 2900 - 2999
        'Provider'        => 3000, // 3000 - 3099
        'Repository'      => 3100, // 3100 - 3199
        'Security'        => 3200, // 3200 - 3299
        'Template'        => 3300, // 3300 - 3399
        'Traits'          => 3400, // 3400 - 3499
        'Console'         => 3500, // 3500 - 3599
        'ConsoleKernel'   => 3600, // 3600 - 3799
        'Http'            => 3800, // 3800 - 3899
        'HttpKernel'      => 3900, // 3900 - 4099
        'Database'        => 4100, // 4100 - 4199 (includes migrations)
        'Mail'            => 4200, // 4200 - 4299
        'FileSystem'      => 4300, // 4300 - 4399
        'JSON'            => 4400, // 4400 - 4499
        'Image'           => 4500, // 4500 - 4599

    ];

    /**
     * Maps actual numeric codes to messages.
     * The authoritative list of ALL known status codes and their messages.
     *  This is where you define framework-level messages for each code.
     */
    private const array CODE_MESSAGES = [

        // -------------------------------------------------
        // SERVER (500–599)
        // -------------------------------------------------
        500 => "Server Error: Internal server error",
        501 => "Server Error: General error",
        502 => "Server Error: Invalid input",
        503 => "Server Error: Resource not found",
        504 => "Server Error: Permission denied",

        // -------------------------------------------------
        // CONTAINERS (2100–2199)
        // -------------------------------------------------
        2100 => "Container Error: Initialization failed",
        2101 => "Container Error: Unable to register service",

        // -------------------------------------------------
        // CONTROLLERS (2200–2299)
        // (No migrated codes yet — reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // AUTHENTICATION (2300–2399)
        // -------------------------------------------------
        2300 => "Authentication Error: Authentication failed",

        // -------------------------------------------------
        // ENTITY (2400–2499)
        // -------------------------------------------------
        2400 => "Entity Error: Conflict detected",
        2401 => "Entity Error: Invalid entity",
        2402 => "Entity Error: Entity not found",
        2403 => "Entity Error: Entity already exists",
        2404 => "Entity Error: Duplicate entry violation",
        2405 => "Entity Error: Foreign key constraint violation",
        2409 => "Entity Error: Unknown entity error",

        // -------------------------------------------------
        // ENUM (2500–2599)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // EVENT LISTENER (2600–2699)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // FORM (2700–2799)
        // -------------------------------------------------
        2700 => "Form Error: Validation failed",
        2701 => "Form Error: Missing required fields",
        2709 => "Form Error: Unknown validation error",

        // -------------------------------------------------
        // LOGGING (2800–2899)
        // -------------------------------------------------
        2800 => "Logging Error: General logging failure",
        2801 => "Logging Error: Failed to write log entry",
        2802 => "Logging Error: Invalid log level",
        2803 => "Logging Error: Log parsing error",
        2804 => "Logging Error: Missing log level key",
        2805 => "Logging Error: Invalid log level value",
        2806 => "Logging Error: Invalid JSON provided to converter",
        2807 => "Logging Error: JSON log file not found",
        2808 => "Logging Error: Malformed JSON provided",
        2809 => "Logging Error: Invalid log message",
        2810 => "Logging Error: Invalid log level and message",
        2811 => "Logging Error: Failed to decode activity log JSON",
        2812 => "Logging Error: Invalid activity log entry",
        2813 => "Logging Error: Failed to encode activity log JSON",
        2814 => "Logging Error: Database activity log writer failure",

        // -------------------------------------------------
        // MIDDLEWARE (2900–2999)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // PROVIDER (3000–3099)
        // -------------------------------------------------
        3000 => "Provider Error: Missing or invalid arguments",
        3001 => "Provider Error: Invalid direction parameter",
        3002 => "Provider Error: Initialization target not found",
        3003 => "Provider Error: Unexpected initialization error",

        // -------------------------------------------------
        // REPOSITORY (3100–3199)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // SECURITY (3200–3299)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // TEMPLATE (3300–3399)
        // -------------------------------------------------
        3300 => "Template Error: Rendering failed",
        3301 => "Template Error: Initialization failed",
        3302 => "Template Error: Unexpected rendering error",

        // -------------------------------------------------
        // TRAITS (3400–3499)
        // (reserved)
        // -------------------------------------------------

        // -------------------------------------------------
        // CONSOLE (3500–3599)
        // -------------------------------------------------
        3500 => "Console Error: Command registration failed",
        3501 => "Console Error: Unknown command registration failure",

        // -------------------------------------------------
        // CONSOLE KERNEL (3600–3799)
        // -------------------------------------------------
        3600 => "Console Kernel Error: General processor error",
        3601 => "Console Kernel Error: ErrorProcessor not initialized",
        3602 => "Console Kernel Error: Invalid logger instance provided",
        3603 => "Console Kernel Error: Processor initialization failure",

        // -------------------------------------------------
        // HTTP (3800–3899)
        // (reserved — add when Http-specific errors exist)
        // -------------------------------------------------
        3800 => "HTTP Error: HTTP subsystem failure",

        // -------------------------------------------------
        // HTTP KERNEL (3900–4099)
        // -------------------------------------------------
        3900 => "HTTP Kernel Error: General kernel failure",

        // -------------------------------------------------
        // DATABASE (4100–4199)
        // -------------------------------------------------
        4100 => "Database Error: Migration apply failed",
        4101 => "Database Error: Migration rollback failed",
        4102 => "Database Error: Insert or delete failed",
        4103 => "Database Error: Migration file access error",
        4104 => "Database Error: Migration table creation error",
        4105 => "Database Error: Migration file retrieval error",
        4106 => "Database Error: Unexpected migration file error",
        4107 => "Database Error: PDO error",
        4108 => "Database Error: General database error",
        4109 => "Database Error: Database creation failed",
        4110 => "Database Error: Database user creation failed",
        4111 => "Database Error: Database exists error",
        4112 => "Database Error: Migration execution failed",
        4113 => "Database Error: Unknown migration error",

        // -------------------------------------------------
        // MAIL (4200–4299)
        // -------------------------------------------------
        4200 => "Mail Error: Mail service failure",

        // -------------------------------------------------
        // FILE SYSTEM (4300–4399)
        // -------------------------------------------------
        4300 => "FileSystem Error: Directory missing or not writable",
        4301 => "FileSystem Error: File write failure",
        4302 => "FileSystem Error: File access failure",

        // -------------------------------------------------
        // JSON (4400–4499)
        // -------------------------------------------------
        4400 => "JSON Error: Invalid JSON format",

        // -------------------------------------------------
        // IMAGE (4500–4599)
        // -------------------------------------------------
        4500 => "Image Error: Image processing failed",
        4501 => "Image Error: Unsupported file type",
        4502 => "Image Error: Invalid filename",
        4503 => "Image Error: Upload failed",
        4504 => "Image Error: Image file not found",
        4505 => "Image Error: Deletion failed",
        4506 => "Image Error: Conversion failed",
        4507 => "Image Error: Unable to move file to upload folder",
        4508 => "Image Error: Unexpected image processing failure",
    ];


    // ------------------------------------------------------------
    // PUBLIC API
    // ------------------------------------------------------------

    /**
     * Return the message for a given code.
     */
    public static function getMessage(?int $code): string
    {
        if ($code === null) {
            return "[null] Unknown Error! No Status Code Provided.";
        }

        if (isset(self::CODE_MESSAGES[$code])) {
            return sprintf("[%d] %s", $code, self::CODE_MESSAGES[$code]);
        }

        return sprintf("[%d] Unknown Status Code", $code);
    }

    /**
     * Generates a status code from a category name plus an offset
     *
     * Example:
     *    StatusCodeManager::make('Repository', 3) => 3103)
     */
    public static function make(string $category, int $offset = 0): int
    {
        if (!isset(self::CATEGORY_BASE[$category])) {
            throw new InvalidArgumentException("Unknown category '{$category}'.");
        }

        if ($offset < 0) {
            throw new InvalidArgumentException("Offset cannot be negative.");
        }

        $base = self::CATEGORY_BASE[$category];

        return $base + $offset;

    }

    /**
     * Determines whether a code is valid (i.e., defined).
     */
    public static function isValidCode(int $code): bool
    {
        return isset(self::CODE_MESSAGES[$code]);
    }

    /**
     * Returns the category associated with the given status code.
     *
     * Reverse lookup based on category ranges.
     */
    public static function getCategoryForCode(int $code): string
    {
        foreach (self::CATEGORY_BASE as $category => $base) {

            //
            // ConsoleKernel has a 200-range block:
            //
            if ($category === 'ConsoleKernel') {
                if ($code >= $base && $code < $base + 200) {
                    return $category;
                }
                continue;
            }

            //
            // Standard 100-range block
            //
            if ($code >= $base && $code < $base + 100) {
                return $category;
            }
        }
        return "Unknown";
    }

    public static function getCategories(): array
    {
        return array_keys(self::CATEGORY_BASE);
    }
}


