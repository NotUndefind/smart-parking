<?php

namespace Infrastructure\Persistence\SQL;

use Domain\Repositories\SubscriptionRepositoryInterface;
use Domain\Entities\Subscription;
use PDO;

class MySQLSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(Subscription $subscription): void
    {
        $stmt = $this->pdo->prepare("SELECT id FROM subscriptions WHERE id = :id");
        $stmt->execute(['id' => $subscription->getId()]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $this->pdo->prepare("
                UPDATE subscriptions 
                SET user_id = :user_id,
                    parking_id = :parking_id,
                    creneaux_reserves = :creneaux_reserves,
                    date_debut = :date_debut,
                    date_fin = :date_fin,
                    prix_mensuel = :prix_mensuel,
                    type = :type
                WHERE id = :id
            ");
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO subscriptions (id, user_id, parking_id, creneaux_reserves, date_debut, date_fin, prix_mensuel, type, created_at)
                VALUES (:id, :user_id, :parking_id, :creneaux_reserves, :date_debut, :date_fin, :prix_mensuel, :type, NOW())
            ");
        }

        $stmt->execute([
            'id' => $subscription->getId(),
            'user_id' => $subscription->getUserId(),
            'parking_id' => $subscription->getParkingId(),
            'creneaux_reserves' => json_encode($subscription->getCreneauxReserves()),
            'date_debut' => $subscription->getDateDebut(),
            'date_fin' => $subscription->getDateFin(),
            'prix_mensuel' => $subscription->getPrixMensuel(),
            'type' => $subscription->getType()
        ]);
    }

    public function findById(string $id): ?Subscription
    {
        $stmt = $this->pdo->prepare("SELECT * FROM subscriptions WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findActiveByParking(string $parkingId, int $currentTimestamp): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE parking_id = :parking_id 
            AND date_debut <= :current_timestamp
            AND date_fin >= :current_timestamp
            ORDER BY date_debut DESC
        ");

        $stmt->execute([
            'parking_id' => $parkingId,
            'current_timestamp' => $currentTimestamp
        ]);

        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE user_id = :user_id 
            ORDER BY date_debut DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findByParkingId(string $parkingId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE parking_id = :parking_id 
            ORDER BY date_debut DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findActiveByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions 
            WHERE parking_id = :parking_id 
            AND date_debut <= :fin_mois
            AND date_fin >= :debut_mois
            ORDER BY date_debut DESC
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
        $stmt = $this->pdo->prepare("DELETE FROM subscriptions WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    private function hydrate(array $data): Subscription
    {
        return new Subscription(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            creneauxReserves: json_decode($data['creneaux_reserves'], true),
            dateDebut: (int)$data['date_debut'],
            dateFin: (int)$data['date_fin'],
            prixMensuel: (float)$data['prix_mensuel'],
            type: $data['type']
        );
    }
}