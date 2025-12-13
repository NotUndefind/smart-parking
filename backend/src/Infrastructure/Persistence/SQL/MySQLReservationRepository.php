<?php

namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\ReservationRepositoryInterface;
use Domain\Entities\Reservation;
use PDO;

class MySQLReservationRepository implements ReservationRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Reservation $reservation): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM reservations WHERE id = :id");
        $stmt->execute(['id' => $reservation->getId()]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $this->pdo->prepare("
                UPDATE reservations 
                SET user_id = :user_id,
                    parking_id = :parking_id,
                    debut = :debut,
                    fin = :fin,
                    prix_estime = :prix_estime,
                    statut = :statut
                WHERE id = :id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO reservations (id, user_id, parking_id, debut, fin, prix_estime, statut, created_at)
                VALUES (:id, :user_id, :parking_id, :debut, :fin, :prix_estime, :statut, NOW())
            ");
        }

        $stmt->execute([
            'id' => $reservation->getId(),
            'user_id' => $reservation->getUserId(),
            'parking_id' => $reservation->getParkingId(),
            'debut' => $reservation->getDebut(),
            'fin' => $reservation->getFin(),
            'prix_estime' => $reservation->getPrixEstime(),
            'statut' => $reservation->getStatut()
        ]);
    }

    public function findById(string $id): ?Reservation
    {
        $stmt = $this->pdo->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findActiveByParking(string $parkingId, int $debut, int $fin): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE parking_id = :parking_id 
            AND statut = 'active'
            AND NOT (fin <= :debut OR debut >= :fin)
            ORDER BY debut ASC
        ");

        $stmt->execute([
            'parking_id' => $parkingId,
            'debut' => $debut,
            'fin' => $fin
        ]);

        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
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
            SELECT * FROM reservations 
            WHERE parking_id = :parking_id 
            ORDER BY debut DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findCompletedByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        $stmt = $this->pdo->prepare("
            SELECT * FROM reservations 
            WHERE parking_id = :parking_id 
            AND statut = 'terminee'
            AND fin >= :debut_mois 
            AND fin <= :fin_mois
            ORDER BY fin DESC
        ");

        $stmt->execute([
            'parking_id' => $parkingId,
            'debut_mois' => $debutMois,
            'fin_mois' => $finMois
        ]);

        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM reservations WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $data): Reservation
    {
        return new Reservation(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            debut: (int)$data['debut'],
            fin: (int)$data['fin'],
            prixEstime: (float)$data['prix_estime'],
            statut: $data['statut']
        );
    }
}


