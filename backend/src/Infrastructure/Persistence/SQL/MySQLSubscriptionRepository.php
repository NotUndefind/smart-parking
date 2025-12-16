<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\SQL;

use App\Domain\Repositories\SubscriptionRepositoryInterface;
use App\Domain\Entities\Subscription;
use PDO;

final class MySQLSubscriptionRepository implements SubscriptionRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
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
                    type = :type,
                    price = :price,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_active = :is_active,
                    updated_at = :updated_at
                WHERE id = :id
            ");
            $stmt->execute([
                'id' => $subscription->getId(),
                'user_id' => $subscription->getUserId(),
                'parking_id' => $subscription->getParkingId(),
                'type' => $subscription->getType(),
                'price' => $subscription->getPrice(),
                'start_date' => $subscription->getStartDate(),
                'end_date' => $subscription->getEndDate(),
                'is_active' => $subscription->isActive() ? 1 : 0,
                'updated_at' => $subscription->getUpdatedAt()?->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO subscriptions (id, user_id, parking_id, type, price, start_date, end_date, is_active, created_at)
                VALUES (:id, :user_id, :parking_id, :type, :price, :start_date, :end_date, :is_active, :created_at)
            ");
            $stmt->execute([
                'id' => $subscription->getId(),
                'user_id' => $subscription->getUserId(),
                'parking_id' => $subscription->getParkingId(),
                'type' => $subscription->getType(),
                'price' => $subscription->getPrice(),
                'start_date' => $subscription->getStartDate(),
                'end_date' => $subscription->getEndDate(),
                'is_active' => $subscription->isActive() ? 1 : 0,
                'created_at' => $subscription->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }
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

    public function findByUserId(string $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions
            WHERE user_id = :user_id
            ORDER BY start_date DESC
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
            ORDER BY start_date DESC
        ");
        $stmt->execute(['parking_id' => $parkingId]);
        $data = $stmt->fetchAll();

        return array_map(fn($row) => $this->hydrate($row), $data);
    }

    public function findActiveByUserAndParking(string $userId, string $parkingId, int $timestamp): ?Subscription
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM subscriptions
            WHERE user_id = :user_id
            AND parking_id = :parking_id
            AND is_active = 1
            AND start_date <= :timestamp
            AND end_date >= :timestamp
            ORDER BY start_date DESC
            LIMIT 1
        ");
        $stmt->execute([
            'user_id' => $userId,
            'parking_id' => $parkingId,
            'timestamp' => $timestamp
        ]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->hydrate($data);
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
            parkingId: $data['parking_id'],
            userId: $data['user_id'],
            type: $data['type'],
            price: (float)$data['price'],
            startDate: (int)$data['start_date'],
            endDate: (int)$data['end_date'],
            isActive: (bool)$data['is_active'],
            createdAt: isset($data['created_at']) ? new \DateTimeImmutable($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}
