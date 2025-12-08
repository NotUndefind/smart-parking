<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\UserRepositoryInterface;
use Domain\Entities\User;

class FileUserRepository implements UserRepositoryInterface
{
    private string $filePath;

    public function __construct(string $dataDirectory)
    {
        $this->filePath = rtrim($dataDirectory, '/') . '/users.json';
        $this->ensureFileExists();
    }

    public function save(User $user): void
    {
        $users = $this->loadAll();
        $users[$user->getId()] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'created_at' => $users[$user->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAll($users);
    }

    public function findById(string $id): ?User
    {
        $users = $this->loadAll();

        if (!isset($users[$id])) {
            return null;
        }

        return $this->hydrate($users[$id]);
    }

    public function findByEmail(string $email): ?User
    {
        $users = $this->loadAll();

        foreach ($users as $userData) {
            if ($userData['email'] === $email) {
                return $this->hydrate($userData);
            }
        }

        return null;
    }

    public function delete(string $id): void
    {
        $users = $this->loadAll();

        if (isset($users[$id])) {
            unset($users[$id]);
            $this->saveAll($users);
        }
    }

    public function findAll(): array
    {
        $users = $this->loadAll();
        return array_map(fn($data) => $this->hydrate($data), array_values($users));
    }

    private function loadAll(): array
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $users): void
    {
        $json = json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

    private function hydrate(array $data): User
    {
        return new User(
            id: $data['id'],
            email: $data['email'],
            password: $data['password'],
            nom: $data['nom'],
            prenom: $data['prenom']
        );
    }
}