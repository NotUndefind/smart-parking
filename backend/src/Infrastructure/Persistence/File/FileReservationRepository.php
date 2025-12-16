<?php

namespace Infrastructure\Persistence\File;

use Domain\Repositories\ReservationRepositoryInterface;
use Domain\Entities\Reservation;

class FileReservationRepository implements ReservationRepositoryInterface
{
    private string $filePath;

    public function __construct(string $dataDirectory)
    {
        $this->filePath = rtrim($dataDirectory, '/') . '/reservations.json';
        $this->ensureFileExists();
    }

    public function save(Reservation $reservation): void
    {
        $reservations = $this->loadAll();
        $reservations[$reservation->getId()] = [
            'id' => $reservation->getId(),
            'user_id' => $reservation->getUserId(),
            'parking_id' => $reservation->getParkingId(),
            'debut' => $reservation->getDebut(),
            'fin' => $reservation->getFin(),
            'prix_estime' => $reservation->getPrixEstime(),
            'statut' => $reservation->getStatut(),
            'created_at' => $reservations[$reservation->getId()]['created_at'] ?? date('Y-m-d H:i:s')
        ];

        $this->saveAll($reservations);
    }

    public function findById(string $id): ?Reservation
    {
        $reservations = $this->loadAll();

        if (!isset($reservations[$id])) {
            return null;
        }

        return $this->hydrate($reservations[$id]);
    }

    public function findActiveByParking(string $parkingId, int $debut, int $fin): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] !== $parkingId) {
                continue;
            }

            if ($reservationData['statut'] !== 'active') {
                continue;
            }

            $resDebut = $reservationData['debut'];
            $resFin = $reservationData['fin'];
            $overlap = !($fin <= $resDebut || $debut >= $resFin);

            if ($overlap) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        return $result;
    }

    public function findByUserId(string $userId): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['user_id'] === $userId) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
    }

    public function findByParkingId(string $parkingId): array
    {
        $reservations = $this->loadAll();
        $result = [];

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] === $parkingId) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        usort($result, fn($a, $b) => $b->getDebut() <=> $a->getDebut());

        return $result;
    }

    public function findCompletedByParkingAndMonth(string $parkingId, int $monthTimestamp): array
    {
        $reservations = $this->loadAll();
        $result = [];

        $debutMois = strtotime(date('Y-m-01', $monthTimestamp));
        $finMois = strtotime(date('Y-m-t 23:59:59', $monthTimestamp));

        foreach ($reservations as $reservationData) {
            if ($reservationData['parking_id'] !== $parkingId) {
                continue;
            }

            if ($reservationData['statut'] !== 'terminee') {
                continue;
            }

            $resFin = $reservationData['fin'];

            if ($resFin >= $debutMois && $resFin <= $finMois) {
                $result[] = $this->hydrate($reservationData);
            }
        }

        usort($result, fn($a, $b) => $b->getFin() <=> $a->getFin());

        return $result;
    }

    public function delete(string $id): void
    {
        $reservations = $this->loadAll();

        if (isset($reservations[$id])) {
            unset($reservations[$id]);
            $this->saveAll($reservations);
        }
    }

    private function loadAll(): array
    {
        $json = file_get_contents($this->filePath);
        return json_decode($json, true) ?? [];
    }

    private function saveAll(array $reservations): void
    {
        $json = json_encode($reservations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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