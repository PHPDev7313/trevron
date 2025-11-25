<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\DirectoryScanner;
use JDS\FileSystem\FileDataService;
use JDS\FileSystem\FileDeleter;
use JDS\FileSystem\FileReader;
use JDS\Parsing\JsonDecoder;
use JDS\Parsing\JsonEncoder;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class FileSystemServiceProvider implements ServiceProviderInterface
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        $this->container->add(DirectoryScanner::class)
            ->addArgument(new StringArgument($this->container->get('config')->get('contactPath')));

        $this->container->add(FileReader::class);
        $this->container->add(JsonDecoder::class);
        $this->container->add(JsonEncoder::class);
        $this->container->add(FileDeleter::class);

        $this->container->add(FileDataService::class)
            ->addArguments([
                DirectoryScanner::class,
                FileReader::class,
                JsonDecoder::class
            ]);
    }
}

