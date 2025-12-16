<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SQL;

use App\Domain\Repositories\StationnementRepositoryInterface;
use App\Domain\Entities\Stationnement;
use PDO;

final class MySQLStationnementRepository implements StationnementRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
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
                    reservation_id = :reservation_id,
                    subscription_id = :subscription_id,
                    entry_time = :entry_time,
                    exit_time = :exit_time,
                    final_price = :final_price,
                    penalty_amount = :penalty_amount,
                    status = :status,
                    updated_at = :updated_at
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $stationnement->getId(),
                'user_id' => $stationnement->getUserId(),
                'parking_id' => $stationnement->getParkingId(),
                'reservation_id' => $stationnement->getReservationId(),
                'subscription_id' => $stationnement->getSubscriptionId(),
                'entry_time' => $stationnement->getEntryTime(),
                'exit_time' => $stationnement->getExitTime(),
                'final_price' => $stationnement->getFinalPrice(),
                'penalty_amount' => $stationnement->getPenaltyAmount(),
                'status' => $stationnement->getStatus(),
                'updated_at' => $stationnement->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO stationnements (id, user_id, parking_id, reservation_id, subscription_id, entry_time, exit_time, final_price, penalty_amount, status, created_at)
                VALUES (:id, :user_id, :parking_id, :reservation_id, :subscription_id, :entry_time, :exit_time, :final_price, :penalty_amount, :status, :created_at)
            ");
            $stmt->execute([
                'id' => $stationnement->getId(),
                'user_id' => $stationnement->getUserId(),
                'parking_id' => $stationnement->getParkingId(),
                'reservation_id' => $stationnement->getReservationId(),
                'subscription_id' => $stationnement->getSubscriptionId(),
                'entry_time' => $stationnement->getEntryTime(),
                'exit_time' => $stationnement->getExitTime(),
                'final_price' => $stationnement->getFinalPrice(),
                'penalty_amount' => $stationnement->getPenaltyAmount(),
                'status' => $stationnement->getStatus(),
                'created_at' => $stationnement->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }
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
            AND status = 'active'
            ORDER BY entry_time DESC
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
            ORDER BY entry_time DESC
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
            ORDER BY entry_time DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findActiveByUserAndParking(string $userId, string $parkingId): ?Stationnement
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM stationnements
            WHERE user_id = :user_id
            AND parking_id = :parking_id
            AND status = 'active'
            ORDER BY entry_time DESC
            LIMIT 1
        ");
        $stmt->execute([
            'user_id' => $userId,
            'parking_id' => $parkingId
        ]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
    }

    public function findOutOfTimeSlotByParking(string $parkingId, int $currentTimestamp): array
    {
        $stmt = $this->pdo->prepare("
            SELECT s.*
            FROM stationnements s
            LEFT JOIN reservations r ON r.user_id = s.user_id
                AND r.parking_id = s.parking_id
                AND r.statut = 'active'
                AND s.entry_time >= r.debut
                AND :current_timestamp <= r.fin
            WHERE s.parking_id = :parking_id
            AND s.status = 'active'
            AND r.id IS NULL
            ORDER BY s.entry_time ASC
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
            reservationId: $data['reservation_id'] ?? null,
            subscriptionId: $data['subscription_id'] ?? null,
            entryTime: (int)$data['entry_time'],
            exitTime: isset($data['exit_time']) ? (int)$data['exit_time'] : null,
            finalPrice: (float)($data['final_price'] ?? 0.0),
            penaltyAmount: (float)($data['penalty_amount'] ?? 0.0),
            status: $data['status'] ?? 'active',
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}
