<?php

declare(strict_types=1);

namespace App\Controller;

use App\Api\ApiValidator;
use App\Exceptions\ApiException;
use App\Repository\ServiceLogRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchLogsController
{
    public function __construct(
        private ApiValidator $apiValidator,
        private ServiceLogRepository $serviceLogRepository
    ) {}

    #[Route('/count', methods: ['GET'])]
    public function count(Request $request): Response
    {
        try {
            $operation = $this->apiValidator->validateRequest($request);

            $count = $this->serviceLogRepository->getCountFromRequest($request);

            $response = new Response(
                json_encode(['counter' => $count]),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json']
            );

            $this->apiValidator->validateResponse($operation, $response);

            return $response;
        } catch (ApiException $exception) {
            return $exception->makeResponse();
        }
    }
}
