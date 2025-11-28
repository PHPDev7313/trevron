<?php

use JDS\FileSystem\JsonFileWriter;
use JDS\Json\JsonBuilder;
use JDS\Json\JsonEncoder;
use JDS\ServiceProvider\JsonServiceProvider;

it('registers json writind service into the container', function () {
    $container = new League\Container\Container();

    $provider = new JSONServiceProvider($container);

    $provider->register();

    // attempt to get core classes
    $encoder = $container->get(JsonEncoder::class);
    $builder = $container->get(JsonBuilder::class);
    $fileWriter = $container->get(JsonFileWriter::class);

    expect($encoder)->toBeInstanceOf(JsonEncoder::class)
        ->and($builder)->toBeInstanceOf(JsonBuilder::class)
        ->and($fileWriter)->toBeInstanceOf(JsonFileWriter::class);
});





