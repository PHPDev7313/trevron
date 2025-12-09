<?php

namespace Tests\Stubs\Fakes;

use JDS\Controller\OldAbstractController;
use JDS\Http\Request;
use JDS\Http\Response;
use Psr\Container\ContainerInterface;

class FakeController extends OldAbstractController
{
    public array $receivedArgs = [];

    public function __construct(
        ContainerInterface $container = null
    )
    {
        //
        // ALLOW optional injection in tests
        //
        if ($container !== null) {
            $this->setContainer($container);

        }
    }

    public function setRequest(Request $request): void
    {
        //
        // override parent setRequest to avoid heavy logic
        //
        $this->request = $request;
    }

    public function testAction(...$args): Response
    {
        $this->receivedArgs = $args;

        return new Response("OK", 200);
    }
}


