<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SQL;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use PDO;

final class MySQLUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(User $user): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (id, email, password_hash, first_name, last_name, created_at, updated_at)
            VALUES (:id, :email, :password_hash, :first_name, :last_name, :created_at, :updated_at)
            ON DUPLICATE KEY UPDATE
                email = :email,
                password_hash = :password_hash,
                first_name = :first_name,
                last_name = :last_name,
                updated_at = :updated_at
        ');

        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $user->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $data): User
    {
        return new User(
            id: $data['id'],
            email: $data['email'],
            passwordHash: $data['password_hash'],
            firstName: $data['first_name'],
            lastName: $data['last_name'],
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

