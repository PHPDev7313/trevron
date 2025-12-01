<?php

namespace JDS\Http;

use Psr\Container\ContainerInterface;
use Throwable;

final class StatusCodeManager
{
    private const CODES = [
        // common error codes
        0 => "Success",
        1 => "General Error",
        2 => "Invalid Input",
        3 => "Authentication Failed",
        4 => "Not Found",
        5 => "Permission Denied",
        6 => "Entity Conflict",
        7 => "Internal Server Error",
        8 => "Register Command Failed",
        9 => "Error Applying Migrations",
        10 => "Error Rolling Back Migrations",
        11 => "Database Record Insert/Delete Failed",
        12 => "Migration File Access Failed",
        13 => "Migration Table Creation Failed",
        14 => "Missing or Invalid Arguments Provided To Called Method",
        15 => "Directory does not exist or is not writable",
        16 => "Direction Parameter is missing or invalid",
        17 => "Migration File Retrieval Failed",
        18 => "Unexpected Migration File Error",
        19 => "PDO Error",
        25 => "JSON Format Error",
        26 => "Initialization Not Found",
        29 => "An Unexpected Error Occurred While Checking Initialization File",
        30 => "Database Related Error",
        31 => "Database Creation Error",
        32 => "Database User Creation Error",
        33 => "Database Exists Error",
        40 => "Write File Error",
        50 => "Twig Rendering Error",
        58 => "Twig Initialization Error",
        59 => "Unexpected Twig Rendering Error",
        60 => "Container Initialization Error",
        70 => "Image Processing Error",
        72 => "Image File Type Error",
        73 => "Image Filename Error",
        74 => "Image Upload Error",
        75 => "Image File Not Found Error",
        76 => "Image Deletion Error",
        77 => "Image Conversion Failed Error",
        78 => "Image Move to Upload Folder Error",
        79 => "Unexpected Error During Image Processing",
        80 => "Database:Migration:Migrate Failed",
        89 => "Unknown Database:Migration:Migrate Error",
        90 => "Add Container Service Failed",
        100 => "Unable to access file",
        200 => "Auditor General Error",

        205 => "Failed to Log Entry for Database Logger",

        210 => "Auditor Log Level General Error",
        211 => "Auditor Log Parsing Error",
        212 => "Auditor Log Key 'level' is missing Error",
        213 => "Auditor Log Invalid Log Level (INFO, ERROR, etc.) Error",
        214 => "Invalid data provided to Json Converter",
        215 => "Auditor JSON File Not Found",
        216 => "Invalid JSON provided to Json Converter",
        217 => "Auditor Log Message is invalid",
        218 => "Auditor Log Level and Message are invalid",

        220 => "Error Processor General Error",
        221 => "Error Processor is NOT Initialized",
        222 => "Error Processor: Provided Logger is not an instance of Logger Interface",
        225 => "Error Processor Initialization Failed",
        400 => "Invalid Entity Error",
        401 => "Entity Not Found Error",
        402 => "Entity Already Exists Error",
        403 => "Entity Integrity Constraint Violation: Duplicate Entry Error",
        404 => "Entity Integrity Constraint Violation: Foreign Key Does Not Exist Error",

        409 => "Unknown Entity Error",
        410 => "Form Validation Error",

        415 => "Form Missing Fields Error",

        419 => "Unknown Form Validation Error",

        500 => 'Internal Server Error',
        1109 => "Unknown Command Registration Error",
        1150 => "MailService Error",
        2800 => "Logging",
        2810 => "Failed to decode activity log JSON",
        2815 => "Entity:Log:Entry",
        2820 => "Failed to encode activity log JSON",
        2825 => "Database:Activity:Log:Writer"
    ];

    /**
     * Retrieves a message corresponding to the provided status code, or a default message if the code is invalid or not provided.
     *
     * @param int|null $code The optional status code to retrieve a message for. If null, a generic error message is returned.
     * @return string Returns the message associated with the given code, or a default message if the code is invalid or null.
     */
    public static function getMessage(?int $code=null): string
    {
        // no code provided
        if (is_null($code)) {
            return "Unknown Error! No Status Code Provided.";
        }
        // code exists
        if (self::isValidCode($code)) {
            return sprintf("[%d] %s", $code, self::CODES[$code]);
        }
        // default
        return sprintf("[%d] Unknown Status Code", $code);

    }

    /**
     * Checks if the provided code exists within the predefined set of valid codes.
     *
     * @param int $code The code to be verified.
     * @return bool Returns true if the code exists in the valid codes, otherwise false.
     */
    public static function isValidCode(int $code): bool
    {
        return array_key_exists($code, self::CODES);
    }
}

