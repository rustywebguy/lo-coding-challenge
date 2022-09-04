<?php

declare(strict_types=1);

namespace App\Api;

use App\Exceptions\ApiException;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ApiValidator
{
    private ServerRequestValidator $requestValidator;

    private ResponseValidator $responseValidator;

    public function __construct()
    {
        $builder = (new ValidatorBuilder())->fromJson(Specification::getSpecification());
        $this->requestValidator = $builder->getServerRequestValidator();
        $this->responseValidator = $builder->getResponseValidator();
    }

    public function validateRequest(Request $request): OperationAddress
    {
        $psr17Factory = new Psr17Factory();
        $psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

        $psrRequest = $psrHttpFactory->createRequest($request);

        try {
            return $this->requestValidator->validate($psrRequest);
        } catch (NoOperation $exception) {
            throw ApiException::noOperationFound($exception);
        } catch (ValidationFailed $exception) {
            throw ApiException::failedValidation($exception, 'request');
        }
    }

    public function validateResponse(OperationAddress $operation, Response $response): void
    {
        $psr17Factory = new Psr17Factory();
        $responseBody = $psr17Factory->createStream($response->getContent());
        $response = $psr17Factory->createResponse($response->getStatusCode())->withBody($responseBody);

        try {
            $this->responseValidator->validate($operation, $response);
        } catch (ValidationFailed $exception) {
            ApiException::failedValidation($exception, 'response');
        }
    }
}
