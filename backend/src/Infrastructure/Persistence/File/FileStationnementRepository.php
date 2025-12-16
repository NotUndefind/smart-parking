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
        $stationnements = $this->loadAll();

        if (!isset($stationnements[$id])) {
            return null;
        }

        return $this->hydrate($stationnements[$id]);
    }

    public function findActiveByParkingId(string $parkingId): array
    {
        $stationnements = $this->loadAll();
        $result = [];

        foreach ($stationnements as $stationnementData) {
            if ($stationnementData['parking_id'] === $parkingId && $stationnementData['fin'] === null) {
                $result[] = $this->hydrate($stationnementData);
            }
        }

        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
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
        return $stationnements;
    }

    public function findByParkingId(string $parkingId): array
    {
        $stationnements = $this->loadAll();
        $result = [];

        foreach ($stationnements as $stationnementData) {
            if ($stationnementData['parking_id'] === $parkingId) {
                $result[] = $this->hydrate($stationnementData);
            }
        }

        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
    }

    public function findOutOfTimeSlotByParking(string $parkingId, int $currentTimestamp): array
    {
        $stationnements = $this->loadAll();
        $reservations = $this->loadReservations();
        $result = [];

        foreach ($stationnements as $stationnementData) {
            if ($stationnementData['parking_id'] !== $parkingId || $stationnementData['fin'] !== null) {
                continue;
            }

            $userId = $stationnementData['user_id'];
            $debut = $stationnementData['debut'];
            $hasValidReservation = false;

            foreach ($reservations as $reservationData) {
                if ($reservationData['user_id'] !== $userId ||
                    $reservationData['parking_id'] !== $parkingId ||
                    $reservationData['statut'] !== 'active') {
                    continue;
                }
                if ($debut >= $reservationData['debut'] && $currentTimestamp <= $reservationData['fin']) {
                    $hasValidReservation = true;
                    break;
                }
            }

            if (!$hasValidReservation) {
                $result[] = $this->hydrate($stationnementData);
            }
        }

        usort($result, fn($a, $b) => $a->getDebut() <=> $b->getDebut());

        return $result;
        $stationnements = [];
        $files = glob($this->dataDir . '/*.json');
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $data['parking_id'] === $parkingId) {
                $stationnements[] = $this->hydrate($data);
            }
        }
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
        $stationnements = $this->loadAll();

        if (isset($stationnements[$id])) {
            unset($stationnements[$id]);
            $this->saveAll($stationnements);
        }
    }

    private function loadAll(): array
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $stationnements): void
    {
        $json = json_encode($stationnements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->filePath, $json, LOCK_EX);
    }

    private function loadReservations(): array
    {
        if (!file_exists($this->reservationsFile)) {
            return [];
        }

        $json = file_get_contents($this->reservationsFile);
        return json_decode($json, true) ?? [];
    }

    private function ensureFileExists(): void
    {
        $dir = dirname($this->filePath);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]), LOCK_EX);
        }
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
            entryTime: $data['entry_time'],
            exitTime: $data['exit_time'] ?? null,
            finalPrice: $data['final_price'] ?? 0.0,
            penaltyAmount: $data['penalty_amount'] ?? 0.0,
            status: $data['status'] ?? 'active',
            createdAt: new \DateTimeImmutable($data['created_at']),
            updatedAt: $data['updated_at'] ? new \DateTimeImmutable($data['updated_at']) : null
        );
    }
}

