<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\FileDeleter;
use JDS\FileSystem\FileNameGenerator;
use JDS\FileSystem\FilePathValidator;
use JDS\FileSystem\JsonFileWriter;
use JDS\Json\JsonBuilder;
use JDS\Json\JsonEncoder;
use League\Container\ServiceProvider\AbstractServiceProvider;

class JsonServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    protected array $provides = [
        FilePathValidator::class,
        FileNameGenerator::class,
        JsonFileWriter::class,
        FileDeleter::class,
        JsonEncoder::class,
        JsonBuilder::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->provides, true);
    }

    public function register(): void
    {
        // file system helpers
        $this->container->add(FilePathValidator::class);
        $this->container->add(FileNameGenerator::class);
        $this->container->add(JsonFileWriter::class);
        $this->container->add(FileDeleter::class);

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

