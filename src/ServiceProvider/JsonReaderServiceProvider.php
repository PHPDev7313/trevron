<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\FileLister;
use JDS\FileSystem\FilePathValidator;
use JDS\Json\JsonDecoder;
use JDS\Json\JsonFileReader;
use JDS\Json\JsonLoader;
use JDS\Json\JsonSorter;
use League\Container\Container;

class JsonReaderServiceProvider implements ServiceProviderInterface
{

    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $this->container->add(JsonDecoder::class);
        $this->container->add(JsonFileReader::class);
        $this->container->add(JsonSorter::class);
        $this->container->add(FileLister::class);

        // Reuse validator
        $this->container->add(FilePathValidator::class);

        $this->container->add(JsonLoader::class)
            ->addArguments([
                FileLister::class,
                JsonSorter::class,
                JsonFileReader::class,
                JsonDecoder::class,
                FilePathValidator::class
            ]);
    }
}

