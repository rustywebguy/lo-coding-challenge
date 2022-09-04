<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use RuntimeException;
use Exception;

class ApiException extends RuntimeException
{
    public function __construct(string $message, private int $statusCode, private Exception $exception)
    {
        parent::__construct($message);
    }

    public static function noOperationFound(Exception $exception): self
    {
        return new self(
            'The requested API endpoint was not found.',
            Response::HTTP_NOT_FOUND,
            $exception
        );
    }

    public static function failedValidation(Exception $exception, string $type): self
    {
        return new self(
            sprintf('The %s does not match the require schema', $type),
            Response::HTTP_BAD_REQUEST,
            $exception
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function makeResponse(): Response
    {
        $content = json_encode([
            'message' => $this->getMessage(),
            'error' => $this->exception->getMessage(),
        ]);

        return new Response($content, $this->getStatusCode(), ['Content-Type' => 'application/json']);
    }
}
