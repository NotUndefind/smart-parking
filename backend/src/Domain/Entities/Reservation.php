<?php

declare(strict_types=1);

namespace App\Domain\Entities;

/**
 * Entité Reservation - Logique métier pure
 */
final class Reservation
{
    private string $id;
    private string $userId;
    private string $parkingId;
    private int $startTime;
    private int $endTime;
    private float $estimatedPrice;
    private string $status; // 'active', 'cancelled', 'completed'
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $userId,
        string $parkingId,
        int $startTime,
        int $endTime,
        float $estimatedPrice,
        string $status = "active",
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->parkingId = $parkingId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->estimatedPrice = $estimatedPrice;
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

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function getEndTime(): int
    {
        return $this->endTime;
    }

    public function getEstimatedPrice(): float
    {
        return $this->estimatedPrice;
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

    public function cancel(): void
    {
        if ($this->status === "completed") {
            throw new \RuntimeException(
                "Cannot cancel a completed reservation",
            );
        }
        $this->status = "cancelled";
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function complete(): void
    {
        $this->status = "completed";
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->status === "active";
    }

    public function isOverlapping(int $startTime, int $endTime): bool
    {
        return !($this->endTime <= $startTime || $this->startTime >= $endTime);
    }
}
