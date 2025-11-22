<?php

namespace JDS\ServiceProvider;

use JDS\FileSystem\FileNameGenerator;
use JDS\FileSystem\FilePathValidator;
use JDS\FileSystem\JsonFileWriter;
use JDS\Json\JsonBuilder;
use JDS\Json\JsonEncoder;
use League\Container\Container;

class JsonServiceProvider implements ServiceProviderInterface
{

    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        // file system helpers
        $this->container->add(FilePathValidator::class);
        $this->container->add(FileNameGenerator::class);
        $this->container->add(JsonFileWriter::class);

        // Core JSON services
        $this->container->add(JsonEncoder::class);
        $this->container->add(JsonBuilder::class)
            ->addArguments([
               JsonEncoder::class,
               JsonFileWriter::class,
               FilePathValidator::class,
               FileNameGenerator::class
            ]);

    }
}

