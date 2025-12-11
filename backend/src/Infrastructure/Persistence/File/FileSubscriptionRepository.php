<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entities\Subscription;
use App\Domain\Repositories\SubscriptionRepositoryInterface;

final class FileSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/../../../data/subscriptions')
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save(Subscription $subscription): void
    {
        $filePath = $this->getFilePath($subscription->getId());
        $data = [
            'id' => $subscription->getId(),
            'parking_id' => $subscription->getParkingId(),
            'user_id' => $subscription->getUserId(),
            'type' => $subscription->getType(),
            'price' => $subscription->getPrice(),
            'start_date' => $subscription->getStartDate(),
            'end_date' => $subscription->getEndDate(),
            'is_active' => $subscription->isActive(),
            'created_at' => $subscription->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $subscription->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findById(string $id): ?Subscription
    {
        $filePath = $this->getFilePath($id);
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data ? $this->hydrate($data) : null;
    }

    public function findByUserId(string $userId): array
    {
        $subscriptions = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId) {
                $subscriptions[] = $this->hydrate($data);
            }
        }
        return $subscriptions;
    }

    public function findByParkingId(string $parkingId): array
    {
        $subscriptions = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId) {
                $subscriptions[] = $this->hydrate($data);
            }
        }
        return $subscriptions;
    }

    public function findActiveByUserAndParking(string $userId, string $parkingId, int $timestamp): ?Subscription
    {
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId && $data['parking_id'] === $parkingId) {
                $subscription = $this->hydrate($data);
                if ($subscription->isValidAt($timestamp)) {
                    return $subscription;
                }
            }
        }
        return null;
    }

    public function delete(string $id): void
    {
        $filePath = $this->getFilePath($id);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    private function getFilePath(string $id): string
    {
        return $this->dataDir . '/' . $id . '.json';
    }

    private function hydrate(array $data): Subscription
    {
        return new Subscription(
            id: $data['id'],
            parkingId: $data['parking_id'],
            userId: $data['user_id'],
            type: $data['type'],
            price: $data['price'],
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            isActive: $data['is_active'] ?? true,
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

