<?php
declare(strict_types=1);
/*
 * Trevron Framework - v1.2 FINAL
 *
 * Controller JSON Render Contract
 */

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
    protected array $provides = [
        JsonDecoderInterface::class,
        FilePathValidator::class,
        JsonLoader::class,
    ];

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

