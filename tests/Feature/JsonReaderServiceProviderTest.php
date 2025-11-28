<?php

use JDS\Json\JsonDecoder;
use JDS\Json\JsonLoader;
use JDS\ServiceProvider\JsonReaderServiceProvider;
use League\Container\Container;

it('registers json reading services into the container', function () {
    $container = new Container();

    $provider = new JsonReaderServiceProvider($container);
    $provider->register();

    $decoder = $container->get(JsonDecoder::class);
    $loader = $container->get(JsonLoader::class);
    $lister = $container->get(\JDS\FileSystem\FileLister::class);

    expect($decoder)->toBeInstanceOf(JsonDecoder::class);
    expect($loader)->toBeInstanceOf(JsonLoader::class);
    expect($lister)->toBeInstanceOf(\JDS\FileSystem\FileLister::class);

});


