<?php

declare(strict_types=1);

namespace App\Domain\Entities;

/**
 * Entité Stationnement - Logique métier pure
 */
final class Stationnement
{
    private string $id;
    private string $userId;
    private string $parkingId;
    private ?string $reservationId;
    private ?string $subscriptionId;
    private int $entryTime;
    private ?int $exitTime;
    private float $finalPrice;
    private float $penaltyAmount;
    private string $status; // 'active', 'completed'
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $userId,
        string $parkingId,
        ?string $reservationId,
        ?string $subscriptionId,
        int $entryTime,
        ?int $exitTime = null,
        float $finalPrice = 0.0,
        float $penaltyAmount = 0.0,
        string $status = 'active',
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->reservationId = $reservationId;
        $this->subscriptionId = $subscriptionId;
        $this->entryTime = $entryTime;
        $this->exitTime = $exitTime;
        $this->finalPrice = $finalPrice;
        $this->penaltyAmount = $penaltyAmount;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getParkingId(): string
    {
        return $this->parkingId;
    }

    public function getReservationId(): ?string
    {
        return $this->reservationId;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function getEntryTime(): int
    {
        return $this->entryTime;
    }

    public function getExitTime(): ?int
    {
        return $this->exitTime;
    }

    public function getFinalPrice(): float
    {
        return $this->finalPrice;
    }

    public function getPenaltyAmount(): float
    {
        return $this->penaltyAmount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDurationMinutes(): int
    {
        if ($this->exitTime === null) {
            return time() - $this->entryTime;
        }
        return ($this->exitTime - $this->entryTime) / 60;
    }

    public function exit(int $exitTime, float $finalPrice, float $penaltyAmount = 0.0): void
    {
        $this->exitTime = $exitTime;
        $this->finalPrice = $finalPrice;
        $this->penaltyAmount = $penaltyAmount;
        $this->status = 'completed';
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

