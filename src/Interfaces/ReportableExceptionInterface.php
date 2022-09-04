<?php

declare(strict_types=1);

namespace App\Interfaces;

use Throwable;

interface ReportableExceptionInterface extends Throwable
{
    public function getContext(): array;
}
