<?php

namespace JDS\ServiceProvider;

use JDS\Auditor\LoggerManager;
use JDS\Configuration\Config;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\Handlers\ExceptionHandler;
use JDS\Http\StatusCodeManager;
use JDS\Logging\ActivityLogger;
use JDS\Logging\ExceptionLogger;
use JDS\Processing\ErrorProcessor;
use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class LoggingServiceProvider implements ServiceProviderInterface
{

    public function __construct(private readonly Container $container)
    {
    }

    /**
     * @var array<string>
     */
    private $provides = [
        'loggerFactory',
        'basicLogger',
        'ExceptionLogger',
        'manager',
        ActivityLogger::class,
        ExceptionLogger::class,
        ErrorProcessor::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        $config = $this->container->get(Config::class);

        //
        // 1. Build Monolog Loggers from config
        //

        $loggers = [];
        foreach ($config->get('logging') as $key => $loggerCfg) {
            $logger = new Logger($loggerCfg['name']);
            $logger->pushHandler(
                new StreamHandler(
                    $loggerCfg['path'],
                    Logger::toMonologLevel($loggerCfg['level'])
                )
            );

            $loggers[$key] = $logger;
        }

        $this->container->add('loggerFactory', new ArrayArgument($loggers));

        //
        // 2. ActivityLogger (basic logger)
        //
        $this->container->add('basicLogger', ActivityLogger::class)
            ->addArgument($loggers['basic'] ?? null);

        //
        // 3. ExceptionLogger
        //
        $this->container->add('ExceptionLogger', ExceptionLogger::class)
            ->addArguments([
                $loggers['exception'] ?? null,
                StatusCodeManager::class,
                $config->isProduction(),
            ]);

        //
        // 4. Initialize global exception + error processors
        //
        ExceptionHandler::initializeWithEnvironment($config->get('environment'));
        ErrorProcessor::initialize($this->container->get('ExceptionLogger'));

        //
        // 5. LoggerManager (audit Logger + registry)
        //
        $manager = new LoggerManager();

        $auditFile = $config->get('basicPath') . '/' . $config->get('logPath') . '/' . $config->get('auditLog');
        // Create audit Logger
        $auditLogger = new Logger('audit');
        $auditHandler = new StreamHandler(
            $auditFile, Level::Info
        );
        $auditHandler->setFormatter(new JsonFormatter());
        $auditLogger->pushHandler($auditHandler);

        $manager->registerLogger('audit', $auditLogger);

        // Register manager in container
        $this->container->add('manager', $manager);
    }
}

