<?php

declare(strict_types=1);

namespace Tests\Modules;

use App\Exceptions\TextFileReaderException;
use App\Modules\TextFileReader;
use PHPUnit\Framework\TestCase;

class TextFileReaderTest extends TestCase
{
    public function testCanReadTextFile(): void
    {
        $reader = TextFileReader::fromFilename(__DIR__.'/data/log-test.txt', 10, 0);

        $this->assertInstanceOf(\Generator::class, $reader->getNextBatch());
        $this->assertEquals(21, $reader->getRowsCount());
        $this->assertEquals(0, $reader->getLinesReadCount());
    }

    public function testItThrowsExceptionOnNotExistingFile(): void
    {
        $this->expectException(TextFileReaderException::class);
        $this->expectExceptionMessage('File cannot be opened!');

        TextFileReader::fromFilename(__DIR__.'/data/log-test', 10, 0);
    }

    public function testItThrowsExceptionOnADirectory(): void
    {
        $this->expectException(TextFileReaderException::class);
        $this->expectExceptionMessage('Filename provided is a directory!');

        TextFileReader::fromFilename(__DIR__.'/data', 10, 0);
    }
}
