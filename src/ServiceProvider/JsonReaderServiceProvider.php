<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Json\JsonDecoderInterface;
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

    public function register(Container $container): void
    {
        $container->add(JsonDecoderInterface::class, JsonDecoder::class);
        $container->add(JsonFileReader::class);
        $container->add(JsonSorter::class);
        $container->add(FileLister::class);

        // Reuse validator
        $container->add(FilePathValidator::class);

        $container->add(JsonLoader::class)
            ->addArguments([
                FileLister::class,
                JsonSorter::class,
                JsonFileReader::class,
                JsonDecoderInterface::class,
                FilePathValidator::class
            ]);
    }
}

