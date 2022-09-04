<?php

declare(strict_types=1);

namespace App\Interfaces;

use Generator;

interface FileReaderInterface
{
    public function getNextBatch(): Generator;

    public function getRowsCount(): int;

    public function getLinesReadCount(): int;
}
