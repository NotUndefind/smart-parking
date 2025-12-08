<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\StationnementRepositoryInterface;
use Domain\Entities\Stationnement;

class FileStationnementRepository implements StationnementRepositoryInterface
{
    private string $filePath;
    private string $reservationsFile;

    public function __construct(string $dataDirectory)
    {
        $dataDirectory = rtrim($dataDirectory, '/');
        $this->filePath = $dataDirectory . '/stationnements.json';
        $this->reservationsFile = $dataDirectory . '/reservations.json';
        $this->ensureFileExists();
    }

    public function save(Stationnement $stationnement): void
    {
        $stationnements = $this->loadAll();
        $stationnements[$stationnement->getId()] = [
            'id' => $stationnement->getId(),
            'user_id' => $stationnement->getUserId(),
            'parking_id' => $stationnement->getParkingId(),
            'debut' => $stationnement->getDebut(),
            'fin' => $stationnement->getFin(),
            'montant_facture' => $stationnement->getMontantFacture(),
            'penalite' => $stationnement->getPenalite(),
            'created_at' => $stationnements[$stationnement->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAll($stationnements);
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
    }

    public function findByUserId(string $userId): array
    {
        $stationnements = $this->loadAll();
        $result = [];

        foreach ($stationnements as $stationnementData) {
            if ($stationnementData['user_id'] === $userId) {
                $result[] = $this->hydrate($stationnementData);
            }
        }

        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
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