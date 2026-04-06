<?php

namespace App\DTOs;

readonly class BackupOperationDTO
{
    public function __construct(
        public string $filename,
        public string $backupPath,
        public string $databasePath,
    ) {
    }

    public static function fromRequest(array $data, string $backupPath, string $databasePath): self
    {
        return new self(
            filename: $data['filename'],
            backupPath: $backupPath,
            databasePath: $databasePath,
        );
    }

    public function getFullPath(): string
    {
        return $this->backupPath . DIRECTORY_SEPARATOR . $this->filename;
    }

    public function fileExists(): bool
    {
        return file_exists($this->getFullPath());
    }
}
