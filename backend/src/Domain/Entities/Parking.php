<?php

declare(strict_types=1);

namespace App\Domain\Entities;

/**
 * Entité Parking - Logique métier pure
 */
final class Parking
{
    private string $id;
    private string $ownerId;
    private string $name;
    private string $address;
    private float $latitude;
    private float $longitude;
    private int $totalSpots;
    private array $tariffs; // [['start_hour' => 0, 'end_hour' => 8, 'price_per_hour' => 2.0], ...]
    private array $schedule; // ['monday' => ['open' => '08:00', 'close' => '20:00'], ...]
    private bool $isActive;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $ownerId,
        string $name,
        string $address,
        float $latitude,
        float $longitude,
        int $totalSpots,
        array $tariffs = [],
        array $schedule = [],
        bool $isActive = true,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->ownerId = $ownerId;
        $this->name = $name;
        $this->address = $address;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->totalSpots = $totalSpots;
        $this->tariffs = $tariffs;
        $this->schedule = $schedule;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOwnerId(): string
    {
        return $this->ownerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getTotalSpots(): int
    {
        return $this->totalSpots;
    }

    public function getTariffs(): array
    {
        return $this->tariffs;
    }

    public function getSchedule(): array
    {
        return $this->schedule;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateTariffs(array $tariffs): void
    {
        $this->tariffs = $tariffs;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateSchedule(array $schedule): void
    {
        $this->schedule = $schedule;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function calculatePrice(int $durationMinutes): float
    {
        $hours = $durationMinutes / 60.0;
        $price = 0.0;
        $currentHour = (int)date('H');

        foreach ($this->tariffs as $tariff) {
            $startHour = $tariff['start_hour'] ?? 0;
            $endHour = $tariff['end_hour'] ?? 24;
            $pricePerHour = $tariff['price_per_hour'] ?? 0.0;

            if ($currentHour >= $startHour && $currentHour < $endHour) {
                $price = $hours * $pricePerHour;
                break;
            }
        }

        return round($price, 2);
    }

    public function isOpenAt(int $timestamp): bool
    {
        if (!$this->isActive) {
            return false;
        }

        $dayOfWeek = strtolower(date('l', $timestamp));
        $daySchedule = $this->schedule[$dayOfWeek] ?? null;

        if ($daySchedule === null || !isset($daySchedule['open'], $daySchedule['close'])) {
            return false;
        }

        $currentTime = date('H:i', $timestamp);
        return $currentTime >= $daySchedule['open'] && $currentTime <= $daySchedule['close'];
    }

    public function calculateDistance(float $latitude, float $longitude): float
    {
        $earthRadius = 6371; // km

        $latDiff = deg2rad($latitude - $this->latitude);
        $lonDiff = deg2rad($longitude - $this->longitude);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
            sin($lonDiff / 2) * sin($lonDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}

