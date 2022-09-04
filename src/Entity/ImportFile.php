<?php

namespace App\Entity;

use App\Repository\ImportFileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImportFileRepository::class)]
class ImportFile
{
    public const INITIATED = 'initiated';
    public const INTERRUPTED = 'interrupted';
    public const FAILED = 'failed';
    public const SUCCESS = 'success';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(nullable: true)]
    private ?int $parsed_items_count = null;

    #[ORM\Column(nullable: true)]
    private ?int $imported_items_count = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getParsedItemsCount():? int
    {
        return $this->parsed_items_count;
    }

    public function setParsedItemsCount(?int $parsedItemsCount): self
    {
        $this->parsed_items_count = $parsedItemsCount;

        return $this;
    }

    public function getImportedItemsCount(): ?int
    {
        return $this->imported_items_count;
    }

    public function setImportedItemsCount(?int $importedItemsCount): self
    {
        $this->imported_items_count = $importedItemsCount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isSuccessful(): bool
    {
        return $this->getStatus() === self::SUCCESS;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
