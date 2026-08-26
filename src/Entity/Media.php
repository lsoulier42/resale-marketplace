<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\Table(name: 'media')]
#[ORM\HasLifecycleCallbacks]
class Media extends AbstractEntity
{
    public function __toString(): string
    {
        return $this->file;
    }

    #[ORM\Column(length: 255)]
    private string $file = '';

    #[ORM\Column]
    private int $fileSize = 0;

    #[ORM\Column(length: 128)]
    private string $fileType = '';

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;
        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize): static
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;
        return $this;
    }
}
