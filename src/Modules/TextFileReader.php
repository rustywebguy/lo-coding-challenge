<?php

declare(strict_types=1);

namespace App\Modules;

use App\Exceptions\TextFileReaderException;
use App\Interfaces\FileReaderInterface;
use SplFileObject;
use Generator;
use RuntimeException;
use LogicException;

final class TextFileReader implements FileReaderInterface
{
    private SplFileObject $file;

    private int $linesReadCount = 0;

    public function __construct(private string $filename, private int $batchSize, private ?int $startOffset = 0)
    {
        $this->file = new SplFileObject($this->filename,'r');
    }

    /**
     * @throws TextFileReaderException
     * @throws LogicException
     */
    public static function fromFilename(string $filename, int $batchSize, ?int $startOffset): self
    {
        try {
            return new self($filename, $batchSize, $startOffset);
        } catch (RuntimeException $exception) {
            throw TextFileReaderException::fileCannotBeOpened($filename);
        } catch (LogicException $exception) {
            throw TextFileReaderException::fileNameIsADirectory($filename);
        }
    }

    public function getNextBatch(): Generator
    {
        // reset on every batch
        $this->linesReadCount = 0;

        $this->file->seek($this->startOffset ?? 0);

        while (!$this->file->eof() && $this->linesReadCount < $this->batchSize) {
            $row = trim($this->file->fgets(), PHP_EOL);

            $this->linesReadCount++;

            if (empty($row)) {
                continue;
            }

            yield $row;
        }

        $this->startOffset += $this->batchSize;
    }

    public function getLinesReadCount(): int
    {
        return $this->linesReadCount;
    }

    public function getRowsCount(): int
    {
        $this->file->seek(PHP_INT_MAX);
        return $this->file->key();
    }
}
