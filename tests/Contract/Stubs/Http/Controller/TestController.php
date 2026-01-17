<?php
/*
 * Trevron Framework - v1.2 FINAL
 *
 * Controller Render Contract
 */
declare(strict_types=1);
namespace Tests\Contract\Stubs\Http\Controller;

use JDS\Controller\AbstractController;
use JDS\Http\Response;

class TestController extends AbstractController
{
    public function index(): Response
    {
        return $this->render(
            'test.html.twig',
            ['name' => 'Trevron']
        );
    }
}

