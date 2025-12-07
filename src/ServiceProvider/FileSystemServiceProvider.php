<?php

namespace JDS\ServiceProvider;

use JDS\Contracts\Security\ServiceProvider\ServiceProviderInterface;
use JDS\FileSystem\DirectoryScanner;
use JDS\FileSystem\FileDataService;
use JDS\FileSystem\FileDeleter;
use JDS\FileSystem\FileReader;
use JDS\Json\JsonDecoder;
use JDS\Json\JsonEncoder;
use League\Container\Argument\Literal\StringArgument;
use League\Container\Container;

class FileSystemServiceProvider implements ServiceProviderInterface
{
    /**
     * @var array<string>
     */
    protected array  $povides = [
        DirectoryScanner::class,
        FileReader::class,
        JsonDecoder::class,
        JsonEncoder::class,
        FileDeleter::class,
        FileDataService::class,
    ];

    public function provides(string $id): bool
    {
        return in_array($id, $this->povides, true);
    }

    public function register(Container $container): void
    {
        $container->add(DirectoryScanner::class)
            ->addArgument(new StringArgument($container->get('config')->get('contactPath')));

        $container->add(FileReader::class);
        $container->add(JsonDecoder::class);
        $container->add(JsonEncoder::class);
        $container->add(FileDeleter::class);

        $container->add(FileDataService::class)
            ->addArguments([
                DirectoryScanner::class,
                FileReader::class,
                JsonDecoder::class
            ]);
    }
}

