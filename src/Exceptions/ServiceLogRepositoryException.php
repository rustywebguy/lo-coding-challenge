<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Interfaces\ReportableExceptionInterface;

class ServiceLogRepositoryException extends Exception implements ReportableExceptionInterface
{
    public function __construct(private string $errorMessage, private array $batch, private Exception $dbException)
    {
        parent::__construct($this->errorMessage);
    }

    public static function cannotInsertBatch(array $batch, Exception $dbException): self
    {
        return new self('Cannot insert batch!', $batch, $dbException);
    }

    public function getContext(): array
    {
        return [
            'batch' => json_encode($this->batch),
            'errorMessage' => $this->dbException->getMessage()
        ];
    }
}
