<?php

declare(strict_types=1);

namespace App\Api;

final class Specification
{
    public static function getSpecification(): string
    {
        return file_get_contents(__DIR__.'/../../specs/api.json');
    }
}
