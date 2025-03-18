# Auditor

## Overview

### Overview of Audit Logging

Audit Logging is a centralized system designed to track and record significant events and activities within an application. By maintaining an organized and robust logging framework, it ensures that critical operations are monitored, errors are traceable, and compliance or debugging requirements are met. This system focuses on flexibility, reusability, and scalability, allowing different components of the application to log information consistently across various destinations (e.g., files, databases, external services).

---

### Primary Goals of the Audit Logging System

1. **Centralized Management**:
    - Provide a single manager to register and access multiple loggers.
    - Simplify how logging is performed across the application by centralizing control.

2. **Event Tracking**:
    - Record who did what, when it happened, and the results.
    - Track changes to sensitive data, user actions (e.g., login/logout), or system events (e.g., resource failures).

3. **Flexibility**:
    - Allow for multiple logging targets, such as files, databases, or 3rd-party APIs (like Elasticsearch or Logstash).
    - Support for varying levels of detail, such as `INFO`, `WARNING`, `ERROR`, or custom event types.

4. **Seamless Integration**:
    - Work with industry-standard logging libraries like Monolog for PSR-3 compliance.
    - Allow developers to plug in custom loggers or adapters when necessary.

5. **Scalability**:
    - Support the registration of multiple named loggers (e.g., `audit`, `error`, `security`) for different use cases.
    - Ensure components can independently access only the loggers they need.

6. **Error Handling**:
    - Clearly indicate missing loggers or misconfigurations through managed exceptions.
    - Ensure no critical logging data is lost due to unregistered or improperly initialized loggers.

---

### How the Audit Logging System Works

1. **Logger Registration**:
    - Loggers are added to a centralized system (e.g., `LoggerManager`) with unique names.
    - Developers can configure loggers to log to specific destinations (e.g., a file logger for audits, a database logger for transactions).

2. **Event Logging**:
    - Once registered, loggers can be retrieved by name and used to log events. A logger may capture information such as:
        - Event type (e.g., user login, data modification).
        - Event metadata (e.g., timestamp, user ID).
        - Additional details (e.g., the result of an operation or contextual information).

3. **Centralized Access**:
    - The application can request a specific logger by name through a unified access point.
    - For example, components can separately access the `audit` logger or the `error` logger without knowing their internal configurations.

4. **Custom Wrappers**:
    - All loggers are wrapped under a shared logging interface or wrapper that standardizes behavior (e.g., unified methods for logging messages with structured metadata).

5. **Different Log Levels**:
    - Loggers typically support multiple levels of logging, such as:
        - **INFO**: General events (e.g., user logged in, product added to cart).
        - **WARNING**: Potentially harmful situations (e.g., failed login attempt).
        - **ERROR**: Critical issues (e.g., database connection failure).
        - **CUSTOM**: Application-specific log levels like `AUDIT` or `SECURITY`.

6. **Error and Exception Tracking**:
    - Provides meaningful error messages when:
        - A logger is not registered but is requested for use.
        - An issue occurs during the logging process (e.g., file permissions problems).

---

### Use Cases for Audit Logging

1. **User Activity Tracking**:
    - Tracking user actions like login attempts, role updates, and data modifications.
    - Ensuring logs provide a trail for auditing purposes (e.g., who accessed what data and when).

2. **Compliance and Security**:
    - Auditing behavior to meet compliance requirements (e.g., GDPR, HIPAA).
    - Logging security events like unauthorized access attempts or configuration changes.

3. **Error and Debug Logging**:
    - Providing detailed information for error debugging (e.g., stack traces and metadata).
    - Logging unexpected behaviors or system failures.

4. **Performance Insights**:
    - Logging system usage patterns for performance analysis.
    - Monitoring resource bottlenecks or trends in user behavior.

5. **System Monitoring**:
    - Log system events like downtime, failed background tasks, or service interruptions.
    - Act as an early warning system through alerts triggered by specific log levels.

---

### High-Level Flow of Audit Logging

1. **Setup**:
    - Configure the system by setting up necessary loggers (e.g., a file logger for audits, a database logger for transactions).
    - Register these loggers into the central `LoggerManager` with unique identifiers.

2. **Dynamic Access**:
    - Any part of the application can retrieve a logger by name and log events.
    - Loggers standardize how different departments or modules log their operations.

3. **Storage and Analysis**:
    - Logs are stored in destination-specific formats (e.g., files, databases, external logging services).
    - These logs can be consumed or processed later for debugging, compliance, or analytical purposes.

---

### Key Features

1. **Multi-Logger Support**:
    - Developers can register multiple loggers for different use cases and retrieve them as needed.

2. **PSR-3 Compatibility**:
    - The system works with PSR-3 compliant libraries (e.g., Monolog), ensuring flexibility and industry alignment.

3. **Extensibility**:
    - Flexible to add custom loggers in the future (e.g., a cloud-based logger for distributed systems).

4. **Error Management**:
    - Graceful handling of missing loggers or misconfiguration issues.

5. **Ease of Integration**:
    - Designed to integrate seamlessly with dependency injection containers for better modularity.

---

### Benefits of the Audit Logging System

1. **Traceability**: Provides a clear and centralized trail of important events for audits and debugging.
2. **Scalability**: Supports multiple logging use cases, from filesystem logs to distributed systems.
3. **Compliance**: Helps conform to legal and industry regulations for tracking sensitive operations.
4. **Standardization**: Unifies how logging is handled across the application for consistency.
5. **Flexibility**: Allows developers to add/remove loggers and switch destinations easily.
6. **Robust Error Handling**: Provides readable errors when loggers are not correctly configured.

---

Would you like more details on any specific aspect or recommendations on how to get started implementing this in your project? 😊


## Design
Ok now I want to develop a Auditing Event Tracker, Auditing Event Logger that I can put in place for this exact purpose. So lets go through some steps designing such a system before we write any code

Great idea! Designing an **Auditing Event Tracker and Logger** for your system will allow you to maintain a comprehensive audit trail of all critical database changes, track user interactions, and enhance traceability. This process involves breaking down the system into logical components and defining its purpose and responsibilities. Let's work through the design in steps:

---

## **Step 1: Goals and Use Cases**
Before designing the system, let's define **what the Auditing Event Tracker will achieve** and the **real-world scenarios it will cover**:

### Goals
1. **Audit Trail**:
    - Track "who did what and when" for all persistence events.
    - For example:
        - User X updated the `inventory` table.
        - User Y persisted a new record in the `user` table.

2. **Accountability**:
    - Identify the actor responsible for making changes (e.g., the authenticated user or system process).

3. **Comprehensive Event Context**:
    - Log contextual information about the entity being persisted, such as the entity type, affected table, primary key, and changes made.

4. **Decoupling**:
    - Ensure that the auditing logic is decoupled from business logic and persistence logic for maintainability and flexibility.

5. **Scalability**:
    - Support logging events in different formats or destinations (e.g., databases, files, external monitoring services).

---

### Example Use Cases
1. **Database Change Auditing**:
    - Log every `INSERT`, `UPDATE`, and `DELETE` for selected entities (like `inventory`, `user`, `order`).

2. **User Activity Monitoring**:
    - Track what actions were performed by specific users, useful for analytics or forensic investigations.
    - Example: `User->123` deleted inventory `ID=456`.

3. **Regulatory Compliance**:
    - Maintain auditable logs required for compliance standards (e.g., GDPR, SOX) or organizational policies.

4. **Error Tracking**:
    - Capture unusual or unexpected events as part of the audit trail (e.g., persistence errors).

---

## **Step 2: System Components**
To implement an Auditing Event Tracker, we need to clearly define its components, responsibilities, and flows:

### 1. **Auditable Entities**
- Specify which entities and events (e.g., `INSERT`, `UPDATE`, `DELETE`) should trigger auditing. For example:
    - `Inventory`: Track all inserts and updates for stock and pricing changes.
    - `User`: Track when a user is created or updated.

- Each entity should provide metadata about itself for auditing:
    - Entity type (e.g., `Inventory`, `User`).
    - Primary key (`id` and/or `user_id`).
    - List of changes (if `UPDATE`) or initial values (if `INSERT`).

---

### 2. **Auditing Events**
- Define events that track changes made to entities. Each auditing event should include:
    - **Actor**: The user or system process responsible for the change.
    - **Entity**: The entity being modified or inserted.
    - **Event Type**: `INSERT`, `UPDATE`, or `DELETE` (or custom types if needed).
    - **Change Details**: A summary of the changes or initial data.

Example Event Structure:
```json
{
   "eventType": "UPDATE",
   "entityType": "Inventory",
   "primaryKey": { "id": 123 },
   "actor": "User->x",
   "timestamp": "2023-11-01T12:00:00Z",
   "changes": {
      "stock": { "old": 50, "new": 75 },
      "price": { "old": 20.99, "new": 22.99 }
   }
}
```

---

### 3. **Event Tracker**
- The **event tracker** listens for relevant persistence events (like `PostPersist`) and gathers context about the operation, including:
    - Current entity.
    - User performing the action.
    - Nature of changes made.

- This component is responsible for packaging an event into a consistent format and passing it downstream to the event logger.

---

### 4. **Event Logger**
- The **Event Logger** takes the audit event generated by the tracker and stores it or passes it along for further processing.

- The default logger will:
    - Save audit records in a dedicated `audit_log` table.
    - Optionally log to other sinks like log files, monitoring services (e.g., Grafana, ELK), or message queues.

---

### 5. **Persistence (Optional)**
- Design a dedicated `audit_log` database table (or similar logging destination) to store audit events.
- Basic schema for the audit log might look like this:
```sql
CREATE TABLE audit_log (
         id BIGINT AUTO_INCREMENT PRIMARY KEY,
         event_type VARCHAR(20) NOT NULL,
         entity_type VARCHAR(50) NOT NULL,
         primary_key JSON NOT NULL,
         actor VARCHAR(50) NOT NULL,
         timestamp DATETIME NOT NULL,
         changes JSON NOT NULL
     );
```

---

### 6. **Dispatcher and Listener Coordination**
- Ensure the `PostPersist` event dispatches sufficient data so the tracker and logger can function.
- Register a `TrackerListener` to listen for `PostPersist` and handle auditing.

---

## **Step 3: Workflow Design**
Here’s how the `Auditing Event Tracker` would integrate with your existing system:

1. **User/Actor Initiates a Database Action**:
    - A user (or process) triggers an `INSERT`, `UPDATE`, or `DELETE`.

2. **Mapper Executes Statement**:
    - `executeStatement()` saves or updates the entity in the database.

3. **PostPersist Event is Dispatched**:
    - The `PostPersist` event dispatches the entity and user performing the action.

4. **Tracker Gathers Context**:
    - The `Auditing Event Tracker` listens for `PostPersist` and extracts:
        - The entity type and primary key.
        - The actor.
        - Change details (if applicable).
        - The event type.

5. **Event is Logged**:
    - The tracker packages everything into an event and forwards it to a logger.
    - The logger writes the event to the `audit_log` database table (or other destinations, like a log file or external service).

---

## **Step 4: Event Example in Action**
Here’s an example sequence for tracking an inventory update:

1. **User Interaction**:
    - `User->123` updates inventory `ID=456`—they change the stock to 75.

2. **Database Operation**:
    - The `executeStatement` updates the row for `ID=456`.
    - The `DataMapper::save()` sets the `id` and dispatches a `PostPersist` event.

3. **Tracker Creates an Audit Event**:
    - The `PostPersist` listener processes the entity and detects the update:
        - `eventType: "UPDATE"`
        - `entityType: "Inventory"`
        - `primaryKey: {"id": 456}`
        - `actor: "User->123"`
        - `changes: {"stock": {"old": 50, "new": 75}}`
    - The tracker passes this to the logger.

4. **Logger Writes to Audit Destination**:
    - The logger saves the audit event to the `audit_log` table:
```json
{
        "eventType": "UPDATE",
        "entityType": "Inventory",
        "primaryKey": { "id": 456 },
        "actor": "User->123",
        "timestamp": "2023-11-01T12:00:00Z",
        "changes": { "stock": { "old": 50, "new": 75 } }
     }
```

---

## **Step 5: Considerations**
- **Scalability**:
    - Use a queue system (like RabbitMQ or Kafka) if logging becomes a bottleneck in high-traffic systems.

- **Security**:
    - Ensure the audit logs are protected from unauthorized modification or access.

- **Integration**:
    - Decide whether to integrate the audit logger directly into `PostPersist` listeners or use separate pipelines (e.g., background jobs).

---

This structured approach sets a solid foundation for implementing your auditing system. From here, you can proceed to write the necessary interfaces and components. Would you like to lay out specific interfaces for the Tracker and Logger next?

### Tier 2
Great! Now that your **tier-one classes** are established (like `LoggerManager`, `AuditorInterface`, and other foundational components of the audit logging system), we are ready to move to **tier two** activities. These will focus on expanding functionality, ensuring proper integration, making it robust, and preparing it for usage across your application.
Here’s an outline of **what's next**:
## 1. **Unit Testing the Tier-One Classes**
Now that the core architecture is in place, it's crucial to add tests to verify the behavior of your tier-one classes.
### Tasks:
- Use **PHPUnit** or **Pest** for unit tests.
- Write tests for:
    - `LoggerManager`:
        - Test logger registration (`registerLogger`).
        - Test successful retrieval of registered Loggers (`getLogger`).
        - Test exception handling if a logger is not found (`LoggerNotFoundException`).

    - `AuditorInterface`:
        - Mock implementations to ensure compliance contracts are enforced.

    - `CentralizedLogger`:
        - Ensure that all log methods log correctly to the underlying logger.


> **Why?** Unit tests ensure your core components work as expected, even before integration with other modules.
>

## 2. **Dependency Injection (DI) Integration**
Integrate the logging system with your application’s **dependency injection container** (if applicable).
### Tasks:
- Bind `AuditorInterface` to your `LoggerManager` in the DI container so you can access it project-wide.
- Use configurations for side-loading logger details (e.g., destinations, log levels).

For example:
- Laravel: Utilize the Laravel Service Provider to register the logger.
- Symfony: Configure services in `services.yaml`.


> **Why?** DI ensures that all your loggers are modular, injectable, and easy to extend or replace in your project.
>

## 3. **Integration with Logging Destinations**
Expand your system to support **different destinations** and ensure your loggers can handle various scenarios.
### Examples of Possible Destinations:
- **Filesystem**: Write audit logs to text/JSON files.
- **Database**: Store logs directly in a SQL/NoSQL database.
- **Third-Party Systems**: Extend integration with solutions like **Logstash**, **Kibana**, **Elasticsearch**, or **Amazon CloudWatch**.
- **Email Notifications**: Set up error loggers to send critical log messages via email.

### Tasks:
- Implement destination-specific handlers:
    - Database handler (e.g., PDOHandler).
    - External API loggers (e.g., Elasticsearch handler).

- Add destination-specific configuration:
    - File location for file loggers.
    - Database tables or schemas for audit logs.


> **Why?** Different destinations allow your system to serve diverse needs, such as debugging from files, auditing from databases, or monitoring real-time alerts.
>

## 4. **Error and Exception Logging Extensions**
Enhance the logging mechanism to capture **unhandled errors** and **exceptions** seamlessly.
### Tasks:
- Implement integrations for logging PHP exceptions and errors:
    - Use `set_exception_handler()` for capturing unhandled exceptions.
    - Use `set_error_handler()` and `register_shutdown_function()` to log PHP errors like warnings and fatal crashes.

- Channels:
    - Log errors and exceptions to distinct loggers (e.g., `errors` or `crashes`) for easier differentiation.


> **Why?** Proper error logging ensures structured capture of critical events, helping with debugging and compliance.
>

## 5. **Advanced Log Formats**
Enhance log message structure and extensibility.
### Tasks:
- Define a **log structure** for all audit logs:
    - Timestamp.
    - Logger Name (e.g., `audit`, `error`).
    - Event Type (e.g., `CREATE_USER`, `FAILED_LOGIN`).
    - Contextual Data (e.g., user ID, IP address, etc.).

- For JSON-based logs:
    - Easily parse logs for machine processing.
    - Example:
``` json
    {
      "timestamp": "2024-01-01T00:00:00Z",
      "logger": "audit",
      "level": "INFO",
      "event": "USER_LOGIN",
      "context": {
        "user_id": "1234",
        "ip_address": "192.168.0.1"
      }
    }
```
> **Why?** Standardized log formats make system-wide integration easier and provide meaningful data for auditing and debugging.
>

## 6. **Middleware or Global Hooks**
Integrate logging into **middleware** or **global hooks** for handling application-wide events.
### Tasks:
- Add middleware for auditing user actions:
    - Capture request details, like IP address, user-authentication data, and changes made by the user.
    - Record API endpoints accessed or modifications in databases.

- In frameworks like Laravel:
    - Create Middleware to hook into request/response processes.
    - Example: Log every incoming API request and its response status.

- Capture database changes automatically:
    - Use database events or ORM hooks (e.g., Eloquent’s `Observer` pattern for Laravel).


> **Why?** Middleware-based logs ensure high-level consistency for logging all user and system interactions.
>

## 7. **Audit Log Viewing and Management**
Create **organizational tools** to query and manage logged data.
### Tasks:
- **Admin Interface**:
    - Build a web-based admin panel for querying audit logs (e.g., filtering by date, user ID, or event type).
    - Example filters: "All login attempts between Jan 2024 and Feb 2024."

- **Search API**:
    - Expose an API for external consumption to query logs.

- **Log Rotation/Cleanup**:
    - Implement automated cleanup mechanisms to prevent disk/buffer overflows (e.g., using cron jobs or log rotation tools like `logrotate`).


> **Why?** Providing tools to easily visualize and manage logs increases productivity and helps with governance needs.
>

## 8. **Feature-Specific Logging**
Integrate logging into specific **application features or services**.
### Examples:
- **Authentication Module**:
    - Log all user authentications (success or failure) along with user IP addresses.

- **Data Modification**:
    - Log all changes to sensitive records (e.g., user profiles, financial transactions).

- **Third-Party Integrations**:
    - Log requests and responses to/from APIs (e.g., payment gateways).


> **Why?** Embedding logging into specific features ensures all critical operations are tracked at the most granular level.
>

## 9. **Performance Optimization**
As your audit logs grow, keeping performance optimal is critical.
### Tasks:
- Optimize logger handling:
    - Use **asynchronous logging** for high-performance scenarios (e.g., use message queues like RabbitMQ or Redis for batch logging).
    - Separate real-time logging (errors, crashes) from non-urgent tasks (audit entries).

- Ensure efficient storage:
    - Use indexed tables for database logs.
    - Compress large log files.


> **Why?** Audit logging can affect performance if not optimized, especially for high-traffic applications.
>

## 10. **Documentation and Training**
Make the system easier to use, even for new developers joining the team.
### Tasks:
- Write proper **README documentation** (as we've touched on earlier) with:
    - Examples for using `LoggerManager` and setting destinations.
    - Best practices for logging in specific parts of the application.

- Conduct training or onboarding for the team to standardize how and when to use audit logging.


> **Why?** Good documentation and training ensure consistent implementation and prevent misuse of the logging system.
>

## 11. **Observability and Monitoring**
Expand logging into observability patterns for real-time monitoring.
### Tasks:
- Set up tools like **Prometheus**, **Grafana**, or **ELK Stack** to visualize logs and metrics.
- Use monitoring solutions for alerts when critical logs appear (e.g., ERROR or CRITICAL levels).


> **Why?** Real-time observability is key for detecting major issues quickly and ensuring downtime is minimized.
>

### Final Thoughts:
To summarize, next steps revolve around expanding functionality (testing, destinations, middleware), improving developer and system usability (integration, documentation, admin tools), and optimizing for real-world use cases (performance, monitoring). These tasks will ensure your **Audit Logging System** is robust, scalable, and ready for production.




## Structure
- src/
- └── Auditor/
  - ├── LoggerManager.php      // Core centralized logger manager
- ├── CentralizedLogger.php  // Wrapper for Monolog to handle logs dynamically
- ├── Exception/
  - └── LoggerNotFoundException.php // Custom exception for missing loggers
- ├── Interfaces/
  - └── AuditorInterface.php // (Optional) Interface for the logger manager
- └── Handlers/
  - └── PDOHandler.php     // Your custom Monolog handler for Doctrine DBAL logging

## Usage
### DI Container
Finally, integrate the `LoggerManager` and inject pre-configured Monolog loggers using the **DI container**.
#### Example DI Container Setup
``` php
use League\Container\Container;
use App\Auditor\LoggerManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;

// Create the DI container
$container = new Container();

// Register LoggerManager as a shared service
$container->addShared(LoggerManager::class, function () {
    $manager = new LoggerManager();

    // Add an 'audit' logger
    $auditLogger = new Logger('audit');
    $auditFileHandler = new StreamHandler(__DIR__ . '/logs/audit.log', Logger::INFO);
    $auditFileHandler->setFormatter(new JsonFormatter());
    $auditLogger->pushHandler($auditFileHandler);

    $manager->registerLogger('audit', $auditLogger);

    // Add a 'database' logger for persistence auditing
    $databaseLogger = new Logger('database');
    $doctrineConnection = $container->get(\Doctrine\DBAL\Connection::class); // Inject existing connection

    // Use your PDOHandler with the existing Doctrine DBAL connection to log into the database
    $dbHandler = new \App\Auditor\Handlers\PDOHandler(
        $doctrineConnection->getWrappedConnection(), // Pass the PDO connection from Doctrine
        'audit_log_table'
    );
    $databaseLogger->pushHandler($dbHandler);

    $manager->registerLogger('database', $databaseLogger);

    return $manager;
});
```





