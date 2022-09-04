<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecificationController
{
    #[Route('/specification', methods: ['GET'])]
    public function index(): Response
    {
        return new Response(file_get_contents(__DIR__.'/../../specs/build/api.html'));
    }
}
