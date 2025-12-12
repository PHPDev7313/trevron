<?php

namespace Tests\Stubs\Transformers;

use JDS\Controller\AbstractController;
use JDS\Http\Response;

class FakeUserController extends AbstractController
{
    public function show(FakeUser $userId): Response
    {
        //
        // NOTE: parameter name is $userId so it matches route param "userId"
        return new Response(
            "user:" . $userId->id . ":" . $userId->name,
            200
        );
    }
}

