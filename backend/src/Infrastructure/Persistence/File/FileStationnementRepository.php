<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\File;

use App\Domain\Entities\Stationnement;
use App\Domain\Repositories\StationnementRepositoryInterface;

final class FileStationnementRepository implements StationnementRepositoryInterface
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/../../../data/stationnements')
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save(Stationnement $stationnement): void
    {
        $filePath = $this->getFilePath($stationnement->getId());
        $data = [
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
            'updated_at' => $stationnement->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function findById(string $id): ?Stationnement
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
        $stationnements = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId) {
                $stationnements[] = $this->hydrate($data);
            }
        }

        usort($stationnements, fn($a, $b) => $b->getEntryTime() <=> $a->getEntryTime());

        return $stationnements;
    }

    public function findByParkingId(string $parkingId): array
    {
        $stationnements = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId) {
                $stationnements[] = $this->hydrate($data);
            }
        }

        usort($stationnements, fn($a, $b) => $b->getEntryTime() <=> $a->getEntryTime());

        return $stationnements;
    }

    public function findActiveByUserAndParking(string $userId, string $parkingId): ?Stationnement
    {
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['user_id'] === $userId && $data['parking_id'] === $parkingId && $data['status'] === 'active') {
                return $this->hydrate($data);
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
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}
