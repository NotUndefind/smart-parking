<?php

namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\OwnerRepositoryInterface;
use Domain\Entities\Owner;
use PDO;

class MySQLOwnerRepository implements OwnerRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Owner $owner): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM owners WHERE id = :id");
        $stmt->execute(['id' => $owner->getId()]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $this->pdo->prepare("
                UPDATE owners 
                SET email = :email, 
                    password = :password, 
                    nom = :nom, 
                    prenom = :prenom
                WHERE id = :id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO owners (id, email, password, nom, prenom, created_at)
                VALUES (:id, :email, :password, :nom, :prenom, NOW())
            ");
        }

        $stmt->execute([
            'id' => $owner->getId(),
            'email' => $owner->getEmail(),
            'password' => $owner->getPassword(),
            'nom' => $owner->getNom(),
            'prenom' => $owner->getPrenom()
        ]);
    }

    public function findById(string $id): ?Owner
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM owners WHERE id = :id
        ");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findByEmail(string $email): ?Owner
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM owners WHERE email = :email
        ");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM owners WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM owners ORDER BY created_at DESC");
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
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