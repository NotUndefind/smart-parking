<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SQL;

use App\Domain\Entities\Owner;
use App\Domain\Repositories\OwnerRepositoryInterface;
use PDO;

final class MySQLOwnerRepository implements OwnerRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(Owner $owner): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO owners (id, email, password_hash, company_name, first_name, last_name, created_at, updated_at)
            VALUES (:id, :email, :password_hash, :company_name, :first_name, :last_name, :created_at, :updated_at)
            ON DUPLICATE KEY UPDATE
                email = :email,
                password_hash = :password_hash,
                company_name = :company_name,
                first_name = :first_name,
                last_name = :last_name,
                updated_at = :updated_at
        ');

        $stmt->execute([
            'id' => $owner->getId(),
            'email' => $owner->getEmail(),
            'password_hash' => $owner->getPasswordHash(),
            'company_name' => $owner->getCompanyName(),
            'first_name' => $owner->getFirstName(),
            'last_name' => $owner->getLastName(),
            'created_at' => $owner->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $owner->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?Owner
    {
        $stmt = $this->pdo->prepare('SELECT * FROM owners WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findByEmail(string $email): ?Owner
    {
        $stmt = $this->pdo->prepare('SELECT * FROM owners WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM owners WHERE id = :id');
        $stmt->execute(['id' => $id]);
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

