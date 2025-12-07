<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Json\JsonEncoderInterface;
use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\FileDeleter;
use JDS\FileSystem\FileNameGenerator;
use JDS\FileSystem\FilePathValidator;
use JDS\FileSystem\JsonFileWriter;
use JDS\Json\JsonBuilder;
use JDS\Json\JsonEncoder;
use League\Container\Container;

class JsonServiceProvider implements ServiceProviderInterface
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

    public function register(Container $container): void
    {
        // file system helpers
        $container->add(FilePathValidator::class);
        $container->add(FileNameGenerator::class);
        $container->add(JsonFileWriter::class);
        $container->add(FileDeleter::class);

        // Core JSON services
        $container->add(jsonEncoderInterface::class, JsonEncoder::class);
        $container->add(JsonBuilder::class)
            ->addArguments([
               JsonEncoderInterface::class,
               JsonFileWriter::class,
               FilePathValidator::class,
               FileNameGenerator::class
            ]);
    }
}

