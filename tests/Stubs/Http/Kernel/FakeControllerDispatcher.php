<?php

namespace Tests\Stubs\Http\Kernel;

use JDS\Http\ControllerDispatcher;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Transformers\TransformerManager;
use JDS\Validation\MethodParameterValidator;
use Psr\Container\ContainerInterface;


class FakeControllerDispatcher extends ControllerDispatcher
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MethodParameterValidator $validator,
        private readonly TransformerManager $transformerManager,
    )
    {

    }

    public function dispatch(Request $request): Response
    {
        return new Response("controller-response", 200);
    }
}

