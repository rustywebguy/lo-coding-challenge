<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Interfaces\ReportableExceptionInterface;

class ParserException extends Exception implements ReportableExceptionInterface
{
    public function __construct(private string $errorMessage, private string $rowLine)
    {
        parent::__construct($this->errorMessage);
    }

    public static function cannotBeParsed(string $rowLine): self
    {
        return new self('Row cannot be parsed due to none matching pattern', $rowLine);
    }

    public function getContext(): array
    {
        return [
            'rowLine' => $this->rowLine
        ];
    }
}
