<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entities\Owner;
use App\Domain\Repositories\OwnerRepositoryInterface;

final class FileOwnerRepository implements OwnerRepositoryInterface
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/../../../data/owners')
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save(Owner $owner): void
    {
        $filePath = $this->getFilePath($owner->getId());
        $data = [
            'id' => $owner->getId(),
            'email' => $owner->getEmail(),
            'password_hash' => $owner->getPasswordHash(),
            'company_name' => $owner->getCompanyName(),
            'first_name' => $owner->getFirstName(),
            'last_name' => $owner->getLastName(),
            'created_at' => $owner->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $owner->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findById(string $id): ?Owner
    {
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data ? $this->hydrate($data) : null;
    }

    public function findByEmail(string $email): ?Owner
    {
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['email'] === $email) {
                return $this->hydrate($data);
            }
        }
        return null;
    }

    public function delete(string $id): void
    {
        $filePath = $this->getFilePath($id);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getFilePath(string $id): string
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $owners): void
    {
        $json = json_encode($owners, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->filePath, $json, LOCK_EX);
    }

    private function ensureFileExists(): void
    {
        $dir = dirname($this->filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]), LOCK_EX);
        }
    }

    private function hydrate(array $data): Owner
    {
        return new Owner(
            id: $data['id'],
            email: $data['email'],
            passwordHash: $data['password_hash'],
            companyName: $data['company_name'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

