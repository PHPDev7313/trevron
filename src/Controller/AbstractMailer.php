<?php

namespace JDS\Controller;

use JDS\Auditor\CentralizedLogger;
use JDS\Configuration\Config;
use JDS\Exceptions\Controller\ControllerRuntimeException;
use JDS\Processing\ErrorProcessor;
use Psr\Container\ContainerInterface;


abstract class AbstractMailer
{
    protected ?ContainerInterface $container = null;
    protected CentralizedLogger $logger;

    /**
     * Sets the container instance and configures the application mode based on the container's APP_DEV setting.
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
        $this->validateContainer();
        $this->logger = $this->container->get('manager')->getLogger('audit');
    }


    /**
     * Validates the container instance to ensure it has been properly initialized.
     */
    private function validateContainer(): void
    {
        if (!$this->container) {
            $exitCode = 60;
            ErrorProcessor::process(
                new ControllerRuntimeException("Container is not properly initialized.", $exitCode, null),
                $exitCode,
                sprintf("Container is not properly initialized. Code: %d", $exitCode)
            );
            exit($exitCode);
        }
    }

    /**
     * Configuration values
     */
    protected function getConfig(): Config
    {
        return $this->container->get('config');
    }
}

