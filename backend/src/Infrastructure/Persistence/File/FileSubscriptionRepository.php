<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\SubscriptionRepositoryInterface;
use Domain\Entities\Subscription;

class FileSubscriptionRepository implements SubscriptionRepositoryInterface
{
    private string $filePath;

    public function __construct(string $dataDirectory)
    {
        $this->filePath = rtrim($dataDirectory, '/') . '/subscriptions.json';
        $this->ensureFileExists();
    }

    public function save(Subscription $subscription): void
    {
        $subscriptions = $this->loadAll();
        $subscriptions[$subscription->getId()] = [
            'id' => $subscription->getId(),
            'user_id' => $subscription->getUserId(),
            'parking_id' => $subscription->getParkingId(),
            'creneaux_reserves' => $subscription->getCreneauxReserves(),
            'date_debut' => $subscription->getDateDebut(),
            'date_fin' => $subscription->getDateFin(),
            'prix_mensuel' => $subscription->getPrixMensuel(),
            'type' => $subscription->getType(),
            'created_at' => $subscriptions[$subscription->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAll($subscriptions);
    }

    public function findById(string $id): ?Subscription
    {
        $subscriptions = $this->loadAll();

        if (!isset($subscriptions[$id])) {
            return null;
        }

        return $this->hydrate($subscriptions[$id]);
    }

    public function findActiveByParking(string $parkingId, int $currentTimestamp): array
    {
        $subscriptions = $this->loadAll();
        $result = [];

        foreach ($subscriptions as $subscriptionData) {
            if ($subscriptionData['parking_id'] !== $parkingId) {
                continue;
            }

            if ($subscriptionData['date_debut'] <= $currentTimestamp && 
                $subscriptionData['date_fin'] >= $currentTimestamp) {
                $result[] = $this->hydrate($subscriptionData);
            }
        }

        usort($result, fn($a, $b) => $b->getDateDebut() <=> $a->getDateDebut());

        return $result;
    }

    public function findByUserId(string $userId): array
    {
        $subscriptions = $this->loadAll();
        $result = [];

        foreach ($subscriptions as $subscriptionData) {
            if ($subscriptionData['user_id'] === $userId) {
                $result[] = $this->hydrate($subscriptionData);
            }
        }

        usort($result, fn($a, $b) => $b->getDateDebut() <=> $a->getDateDebut());

        return $result;
    }

    public function findByParkingId(string $parkingId): array
    {
        $subscriptions = $this->loadAll();
        $result = [];

        foreach ($subscriptions as $subscriptionData) {
            if ($subscriptionData['parking_id'] === $parkingId) {
                $result[] = $this->hydrate($subscriptionData);
            }
        }

        usort($result, fn($a, $b) => $b->getDateDebut() <=> $a->getDateDebut());

        return $result;
    }

    public function findActiveByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $subscriptions = $this->loadAll();
        $result = [];

        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        foreach ($subscriptions as $subscriptionData) {
            if ($subscriptionData['parking_id'] !== $parkingId) {
                continue;
            }

            $dateDebut = $subscriptionData['date_debut'];
            $dateFin = $subscriptionData['date_fin'];

            if ($dateDebut <= $finMois && $dateFin >= $debutMois) {
                $result[] = $this->hydrate($subscriptionData);
            }
        }

        usort($result, fn($a, $b) => $b->getDateDebut() <=> $a->getDateDebut());

        return $result;
    }

    public function delete(string $id): void
    {
        $subscriptions = $this->loadAll();

        if (isset($subscriptions[$id])) {
            unset($subscriptions[$id]);
            $this->saveAll($subscriptions);
        }
    }

    private function loadAll(): array
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $subscriptions): void
    {
        $json = json_encode($subscriptions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($this->filePath, $json, LOCK_EX);
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
    }

    private function hydrate(array $data): Subscription
    {
        return new Subscription(
            id: $data['id'],
            userId: $data['user_id'],
            parkingId: $data['parking_id'],
            creneauxReserves: $data['creneaux_reserves'],
            dateDebut: (int)$data['date_debut'],
            dateFin: (int)$data['date_fin'],
            prixMensuel: (float)$data['prix_mensuel'],
            type: $data['type']
        );
    }
}