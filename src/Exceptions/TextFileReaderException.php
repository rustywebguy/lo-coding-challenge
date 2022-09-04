<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use App\Interfaces\ReportableExceptionInterface;

class TextFileReaderException extends Exception implements ReportableExceptionInterface
{
    public function __construct(private string $errorMessage, private string $filename)
    {
        parent::__construct($this->errorMessage);
    }

    public static function fileCannotBeOpened(string $filename): self
    {
        return new self('File cannot be opened!', $filename);
    }

    public static function fileNameIsADirectory(string $filename): self
    {
        return new self('Filename provided is a directory!', $filename);
    }

    public function getContext(): array
    {
        return [
            'filename' => $this->filename
        ];
    }
}
