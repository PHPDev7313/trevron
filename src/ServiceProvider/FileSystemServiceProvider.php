<?php

namespace JDS\ServiceProvider;

use JDS\FileSystem\DirectoryScanner;
use JDS\FileSystem\FileDataService;
use JDS\FileSystem\FileDeleter;
use JDS\FileSystem\FileReader;
use JDS\Json\JsonDecoder;
use JDS\Json\JsonEncoder;
use League\Container\Argument\Literal\StringArgument;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\ServiceProviderInterface;

class FileSystemServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
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

