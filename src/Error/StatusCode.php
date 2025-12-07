<?php

namespace JDS\Error;

enum StatusCode: int
{
    // -------------------------------------------------
    // SERVER (500–599)
    // -------------------------------------------------
    case SERVER_INTERNAL_ERROR      = 500;
    case SERVER_GENERAL_ERROR       = 501;
    case SERVER_INVALID_INPUT       = 502;
    case SERVER_NOT_FOUND           = 503;
    case SERVER_PERMISSION_DENIED   = 504;

    // -------------------------------------------------
    // CONTAINERS (2100–2199)
    // -------------------------------------------------
    case CONTAINER_INITIALIZATION_FAILED = 2100;
    case CONTAINER_SERVICE_REGISTRATION_FAILED = 2101;

    // -------------------------------------------------
    // AUTHENTICATION (2300–2399)
    // -------------------------------------------------
    case AUTHENTICATION_FAILED = 2300;

    // -------------------------------------------------
    // ENTITY (2400–2499)
    // -------------------------------------------------
    case ENTITY_CONFLICT                  = 2400;
    case ENTITY_INVALID                   = 2401;
    case ENTITY_NOT_FOUND                 = 2402;
    case ENTITY_ALREADY_EXISTS            = 2403;
    case ENTITY_DUPLICATE_ENTRY           = 2404;
    case ENTITY_FOREIGN_KEY_VIOLATION     = 2405;
    case ENTITY_UNKNOWN_ERROR             = 2409;

    // -------------------------------------------------
    // FORM (2700–2799)
    // -------------------------------------------------
    case FORM_VALIDATION_FAILED           = 2700;
    case FORM_MISSING_FIELDS              = 2701;
    case FORM_UNKNOWN_VALIDATION_ERROR    = 2709;

    // -------------------------------------------------
    // LOGGING (2800–2899)
    // -------------------------------------------------
    case LOGGING_GENERAL_FAILURE                  = 2800;
    case LOGGING_WRITE_ENTRY_FAILED               = 2801;
    case LOGGING_INVALID_LOG_LEVEL                = 2802;
    case LOGGING_PARSING_ERROR                    = 2803;
    case LOGGING_MISSING_LEVEL_KEY                = 2804;
    case LOGGING_INVALID_LEVEL_VALUE              = 2805;
    case LOGGING_INVALID_JSON_TO_CONVERTER        = 2806;
    case LOGGING_JSON_FILE_NOT_FOUND              = 2807;
    case LOGGING_MALFORMED_JSON                   = 2808;
    case LOGGING_INVALID_MESSAGE                  = 2809;
    case LOGGING_INVALID_LEVEL_AND_MESSAGE        = 2810;
    case LOGGING_ACTIVITY_JSON_DECODE_FAILED      = 2811;
    case LOGGING_ACTIVITY_INVALID_ENTRY           = 2812;
    case LOGGING_ACTIVITY_JSON_ENCODE_FAILED      = 2813;
    case LOGGING_ACTIVITY_DB_WRITER_FAILURE       = 2814;

    // -------------------------------------------------
    // PROVIDER (3000–3099)
    // -------------------------------------------------
    case PROVIDER_INVALID_ARGUMENTS        = 3000;
    case PROVIDER_INVALID_DIRECTION_PARAM  = 3001;
    case PROVIDER_INITIALIZATION_TARGET_NOT_FOUND = 3002;
    case PROVIDER_UNEXPECTED_INITIALIZATION_ERROR  = 3003;

    // -------------------------------------------------
    // TEMPLATE (3300–3399)
    // -------------------------------------------------
    case TEMPLATE_RENDERING_FAILED         = 3300;
    case TEMPLATE_INITIALIZATION_FAILED    = 3301;
    case TEMPLATE_UNEXPECTED_RENDERING_ERROR = 3302;

    // -------------------------------------------------
    // CONSOLE (3500–3599)
    // -------------------------------------------------
    case CONSOLE_COMMAND_REGISTRATION_FAILED       = 3500;
    case CONSOLE_UNKNOWN_COMMAND_REGISTRATION_ERROR = 3501;

    // -------------------------------------------------
    // CONSOLE KERNEL (3600–3799)
    // -------------------------------------------------
    case CONSOLE_KERNEL_PROCESSOR_ERROR           = 3600;
    case CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED = 3601;
    case CONSOLE_KERNEL_INVALID_LOGGER_INSTANCE   = 3602;
    case CONSOLE_KERNEL_PROCESSOR_INITIALIZATION_FAILED = 3603;

    // -------------------------------------------------
    // HTTP (3800–3899)
    // -------------------------------------------------
    case HTTP_SUBSYSTEM_FAILURE = 3800;

    // -------------------------------------------------
    // HTTP KERNEL (3900–4099)
    // -------------------------------------------------
    case HTTP_KERNEL_GENERAL_FAILURE = 3900;

    // -------------------------------------------------
    // DATABASE (4100–4199)
    // -------------------------------------------------
    case DATABASE_MIGRATION_APPLY_FAILED          = 4100;
    case DATABASE_MIGRATION_ROLLBACK_FAILED       = 4101;
    case DATABASE_INSERT_OR_DELETE_FAILED         = 4102;
    case DATABASE_MIGRATION_FILE_ACCESS_ERROR     = 4103;
    case DATABASE_MIGRATION_TABLE_CREATION_ERROR  = 4104;
    case DATABASE_MIGRATION_FILE_RETRIEVAL_ERROR  = 4105;
    case DATABASE_UNEXPECTED_MIGRATION_FILE_ERROR = 4106;
    case DATABASE_PDO_ERROR                       = 4107;
    case DATABASE_GENERAL_ERROR                   = 4108;
    case DATABASE_CREATION_FAILED                 = 4109;
    case DATABASE_USER_CREATION_FAILED            = 4110;
    case DATABASE_EXISTS_ERROR                    = 4111;
    case DATABASE_MIGRATION_EXECUTION_FAILED      = 4112;
    case DATABASE_UNKNOWN_MIGRATION_ERROR         = 4113;

    // -------------------------------------------------
    // MAIL (4200–4299)
    // -------------------------------------------------
    case MAIL_SERVICE_FAILURE                     = 4200;

    // -------------------------------------------------
    // FILE SYSTEM (4300–4399)
    // -------------------------------------------------
    case FILESYSTEM_DIRECTORY_NOT_WRITABLE        = 4300;
    case FILESYSTEM_FILE_WRITE_FAILURE            = 4301;
    case FILESYSTEM_FILE_ACCESS_FAILURE           = 4302;

    // -------------------------------------------------
    // JSON (4400–4499)
    // -------------------------------------------------
    case JSON_INVALID_FORMAT                      = 4400;

    // -------------------------------------------------
    // IMAGE (4500–4599)
    // -------------------------------------------------
    case IMAGE_PROCESSING_FAILED                  = 4500;
    case IMAGE_UNSUPPORTED_FILE_TYPE              = 4501;
    case IMAGE_INVALID_FILENAME                   = 4502;
    case IMAGE_UPLOAD_FAILED                      = 4503;
    case IMAGE_FILE_NOT_FOUND                     = 4504;
    case IMAGE_DELETION_FAILED                    = 4505;
    case IMAGE_CONVERSION_FAILED                  = 4506;
    case IMAGE_MOVE_TO_UPLOAD_FAILED              = 4507;
    case IMAGE_UNEXPECTED_PROCESSING_FAILURE      = 4508;

    // -------------------------------------------------
    // Helpers
    // -------------------------------------------------

    public function defaultMessage(): string
    {
        return match ($this) {
            // Server
            self::SERVER_INTERNAL_ERROR    => "Server Error: Internal server error",
            self::SERVER_GENERAL_ERROR     => "Server Error: General error",
            self::SERVER_INVALID_INPUT     => "Server Error: Invalid input",
            self::SERVER_NOT_FOUND         => "Server Error: Resource not found",
            self::SERVER_PERMISSION_DENIED => "Server Error: Permission denied",

            // Containers
            self::CONTAINER_INITIALIZATION_FAILED =>
            "Container Error: Initialization failed",
            self::CONTAINER_SERVICE_REGISTRATION_FAILED =>
            "Container Error: Unable to register service",

            // Authentication
            self::AUTHENTICATION_FAILED =>
            "Authentication Error: Authentication failed",

            // Entity
            self::ENTITY_CONFLICT =>
            "Entity Error: Conflict detected",
            self::ENTITY_INVALID =>
            "Entity Error: Invalid entity",
            self::ENTITY_NOT_FOUND =>
            "Entity Error: Entity not found",
            self::ENTITY_ALREADY_EXISTS =>
            "Entity Error: Entity already exists",
            self::ENTITY_DUPLICATE_ENTRY =>
            "Entity Error: Duplicate entry violation",
            self::ENTITY_FOREIGN_KEY_VIOLATION =>
            "Entity Error: Foreign key constraint violation",
            self::ENTITY_UNKNOWN_ERROR =>
            "Entity Error: Unknown entity error",

            // Form
            self::FORM_VALIDATION_FAILED =>
            "Form Error: Validation failed",
            self::FORM_MISSING_FIELDS =>
            "Form Error: Missing required fields",
            self::FORM_UNKNOWN_VALIDATION_ERROR =>
            "Form Error: Unknown validation error",

            // Logging
            self::LOGGING_GENERAL_FAILURE =>
            "Logging Error: General logging failure",
            self::LOGGING_WRITE_ENTRY_FAILED =>
            "Logging Error: Failed to write log entry",
            self::LOGGING_INVALID_LOG_LEVEL =>
            "Logging Error: Invalid log level",
            self::LOGGING_PARSING_ERROR =>
            "Logging Error: Log parsing error",
            self::LOGGING_MISSING_LEVEL_KEY =>
            "Logging Error: Missing log level key",
            self::LOGGING_INVALID_LEVEL_VALUE =>
            "Logging Error: Invalid log level value",
            self::LOGGING_INVALID_JSON_TO_CONVERTER =>
            "Logging Error: Invalid JSON provided to converter",
            self::LOGGING_JSON_FILE_NOT_FOUND =>
            "Logging Error: JSON log file not found",
            self::LOGGING_MALFORMED_JSON =>
            "Logging Error: Malformed JSON provided",
            self::LOGGING_INVALID_MESSAGE =>
            "Logging Error: Invalid log message",
            self::LOGGING_INVALID_LEVEL_AND_MESSAGE =>
            "Logging Error: Invalid log level and message",
            self::LOGGING_ACTIVITY_JSON_DECODE_FAILED =>
            "Logging Error: Failed to decode activity log JSON",
            self::LOGGING_ACTIVITY_INVALID_ENTRY =>
            "Logging Error: Invalid activity log entry",
            self::LOGGING_ACTIVITY_JSON_ENCODE_FAILED =>
            "Logging Error: Failed to encode activity log JSON",
            self::LOGGING_ACTIVITY_DB_WRITER_FAILURE =>
            "Logging Error: Database activity log writer failure",

            // Provider
            self::PROVIDER_INVALID_ARGUMENTS =>
            "Provider Error: Missing or invalid arguments",
            self::PROVIDER_INVALID_DIRECTION_PARAM =>
            "Provider Error: Invalid direction parameter",
            self::PROVIDER_INITIALIZATION_TARGET_NOT_FOUND =>
            "Provider Error: Initialization target not found",
            self::PROVIDER_UNEXPECTED_INITIALIZATION_ERROR =>
            "Provider Error: Unexpected initialization error",

            // Template
            self::TEMPLATE_RENDERING_FAILED =>
            "Template Error: Rendering failed",
            self::TEMPLATE_INITIALIZATION_FAILED =>
            "Template Error: Initialization failed",
            self::TEMPLATE_UNEXPECTED_RENDERING_ERROR =>
            "Template Error: Unexpected rendering error",

            // Console
            self::CONSOLE_COMMAND_REGISTRATION_FAILED =>
            "Console Error: Command registration failed",
            self::CONSOLE_UNKNOWN_COMMAND_REGISTRATION_ERROR =>
            "Console Error: Unknown command registration failure",

            // Console Kernel
            self::CONSOLE_KERNEL_PROCESSOR_ERROR =>
            "Console Kernel Error: General processor error",
            self::CONSOLE_KERNEL_PROCESSOR_NOT_INITIALIZED =>
            "Console Kernel Error: ErrorProcessor not initialized",
            self::CONSOLE_KERNEL_INVALID_LOGGER_INSTANCE =>
            "Console Kernel Error: Invalid logger instance provided",
            self::CONSOLE_KERNEL_PROCESSOR_INITIALIZATION_FAILED =>
            "Console Kernel Error: Processor initialization failure",

            // HTTP
            self::HTTP_SUBSYSTEM_FAILURE =>
            "HTTP Error: HTTP subsystem failure",

            // HTTP Kernel
            self::HTTP_KERNEL_GENERAL_FAILURE =>
            "HTTP Kernel Error: General kernel failure",

            // Database
            self::DATABASE_MIGRATION_APPLY_FAILED =>
            "Database Error: Migration apply failed",
            self::DATABASE_MIGRATION_ROLLBACK_FAILED =>
            "Database Error: Migration rollback failed",
            self::DATABASE_INSERT_OR_DELETE_FAILED =>
            "Database Error: Insert or delete failed",
            self::DATABASE_MIGRATION_FILE_ACCESS_ERROR =>
            "Database Error: Migration file access error",
            self::DATABASE_MIGRATION_TABLE_CREATION_ERROR =>
            "Database Error: Migration table creation error",
            self::DATABASE_MIGRATION_FILE_RETRIEVAL_ERROR =>
            "Database Error: Migration file retrieval error",
            self::DATABASE_UNEXPECTED_MIGRATION_FILE_ERROR =>
            "Database Error: Unexpected migration file error",
            self::DATABASE_PDO_ERROR =>
            "Database Error: PDO error",
            self::DATABASE_GENERAL_ERROR =>
            "Database Error: General database error",
            self::DATABASE_CREATION_FAILED =>
            "Database Error: Database creation failed",
            self::DATABASE_USER_CREATION_FAILED =>
            "Database Error: Database user creation failed",
            self::DATABASE_EXISTS_ERROR =>
            "Database Error: Database exists error",
            self::DATABASE_MIGRATION_EXECUTION_FAILED =>
            "Database Error: Migration execution failed",
            self::DATABASE_UNKNOWN_MIGRATION_ERROR =>
            "Database Error: Unknown migration error",

            // Mail
            self::MAIL_SERVICE_FAILURE =>
            "Mail Error: Mail service failure",

            // FileSystem
            self::FILESYSTEM_DIRECTORY_NOT_WRITABLE =>
            "FileSystem Error: Directory missing or not writable",
            self::FILESYSTEM_FILE_WRITE_FAILURE =>
            "FileSystem Error: File write failure",
            self::FILESYSTEM_FILE_ACCESS_FAILURE =>
            "FileSystem Error: File access failure",

            // JSON
            self::JSON_INVALID_FORMAT =>
            "JSON Error: Invalid JSON format",

            // Image
            self::IMAGE_PROCESSING_FAILED =>
            "Image Error: Image processing failed",
            self::IMAGE_UNSUPPORTED_FILE_TYPE =>
            "Image Error: Unsupported file type",
            self::IMAGE_INVALID_FILENAME =>
            "Image Error: Invalid filename",
            self::IMAGE_UPLOAD_FAILED =>
            "Image Error: Upload failed",
            self::IMAGE_FILE_NOT_FOUND =>
            "Image Error: Image file not found",
            self::IMAGE_DELETION_FAILED =>
            "Image Error: Deletion failed",
            self::IMAGE_CONVERSION_FAILED =>
            "Image Error: Conversion failed",
            self::IMAGE_MOVE_TO_UPLOAD_FAILED =>
            "Image Error: Unable to move file to upload folder",
            self::IMAGE_UNEXPECTED_PROCESSING_FAILURE =>
            "Image Error: Unexpected image processing failure",
        };
    }

    public function category(): StatusCategory
    {
        return StatusCategory::fromCode($this->value);
    }

    public function formatted(): string
    {
        return sprintf('[%d] %s', $this->value, $this->defaultMessage());
    }
}

