<?php

namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\StationnementRepositoryInterface;
use Domain\Entities\Stationnement;
use PDO;

class MySQLStationnementRepository implements StationnementRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Stationnement $stationnement): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM stationnements WHERE id = :id");
        $stmt->execute(['id' => $stationnement->getId()]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $this->pdo->prepare("
                UPDATE stationnements 
                SET user_id = :user_id,
                    parking_id = :parking_id,
                    debut = :debut,
                    fin = :fin,
                    montant_facture = :montant_facture,
                    penalite = :penalite
                WHERE id = :id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO stationnements (id, user_id, parking_id, debut, fin, montant_facture, penalite, created_at)
                VALUES (:id, :user_id, :parking_id, :debut, :fin, :montant_facture, :penalite, NOW())
            ");
        }

        $stmt->execute([
            'id' => $stationnement->getId(),
            'user_id' => $stationnement->getUserId(),
            'parking_id' => $stationnement->getParkingId(),
            'debut' => $stationnement->getDebut(),
            'fin' => $stationnement->getFin(),
            'montant_facture' => $stationnement->getMontantFacture(),
            'penalite' => $stationnement->getPenalite()
        ]);
    }

    public function findById(string $id): ?Stationnement
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stationnements WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findActiveByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stationnements 
            WHERE parking_id = :parking_id 
            AND fin IS NULL
            ORDER BY debut DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stationnements 
            WHERE user_id = :user_id 
            ORDER BY debut DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stationnements 
            WHERE parking_id = :parking_id 
            ORDER BY debut DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findOutOfTimeSlotByParking(string $parkingId, int $currentTimestamp): array
    {  
        $stmt = $this->pdo->prepare("
            SELECT s.* 
            FROM stationnements s
            LEFT JOIN reservations r ON r.user_id = s.user_id 
                AND r.parking_id = s.parking_id
                AND r.statut = 'active'
                AND s.debut >= r.debut 
                AND :current_timestamp <= r.fin
            WHERE s.parking_id = :parking_id
            AND s.fin IS NULL
            AND r.id IS NULL
            ORDER BY s.debut ASC
        ");

        $stmt->execute([
            'parking_id' => $parkingId,
            'current_timestamp' => $currentTimestamp
        ]);

        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM stationnements WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $data): Stationnement
    {
        return new Stationnement(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            debut: (int)$data['debut'],
            fin: $data['fin'] !== null ? (int)$data['fin'] : null,
            montantFacture: $data['montant_facture'] !== null ? (float)$data['montant_facture'] : null,
            penalite: (float)$data['penalite']
        );
    }
}


