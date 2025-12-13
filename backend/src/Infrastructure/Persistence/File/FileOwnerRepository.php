<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\OwnerRepositoryInterface;
use Domain\Entities\Owner;

class FileOwnerRepository implements OwnerRepositoryInterface
{
    private string $filePath;

    public function __construct(string $dataDirectory)
    {
        $this->filePath = rtrim($dataDirectory, '/') . '/owners.json';
        $this->ensureFileExists();
    }

    public function save(Owner $owner): void
    {
        $owners = $this->loadAll();
        $owners[$owner->getId()] = [
            'id' => $owner->getId(),
            'email' => $owner->getEmail(),
            'password' => $owner->getPassword(),
            'nom' => $owner->getNom(),
            'prenom' => $owner->getPrenom(),
            'created_at' => $owners[$owner->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAll($owners);
    }

    public function findById(string $id): ?Owner
    {
        $owners = $this->loadAll();

        if (!isset($owners[$id])) {
            return null;
        }

        return $this->hydrate($owners[$id]);
    }

    public function findByEmail(string $email): ?Owner
    {
        $owners = $this->loadAll();

        foreach ($owners as $ownerData) {
            if ($ownerData['email'] === $email) {
                return $this->hydrate($ownerData);
            }
        }

        return null;
    }

    public function delete(string $id): void
    {
        $owners = $this->loadAll();

        if (isset($owners[$id])) {
            unset($owners[$id]);
            $this->saveAll($owners);
        }
    }

    public function findAll(): array
    {
        $owners = $this->loadAll();
        return array_map(fn($data) => $this->hydrate($data), array_values($owners));
    }

    private function loadAll(): array
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
            password: $data['password'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }
}


