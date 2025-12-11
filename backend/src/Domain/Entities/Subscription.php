<?php

declare(strict_types=1);

namespace App\Domain\Entities;

/**
 * Entité Subscription - Logique métier pure
 */
final class Subscription
{
    private string $id;
    private string $parkingId;
    private string $userId;
    private string $type; // 'daily', 'weekly', 'monthly', 'yearly'
    private float $price;
    private int $startDate;
    private int $endDate;
    private bool $isActive;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $parkingId,
        string $userId,
        string $type,
        float $price,
        int $startDate,
        int $endDate,
        bool $isActive = true,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->parkingId = $parkingId;
        $this->userId = $userId;
        $this->type = $type;
        $this->price = $price;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParkingId(): string
    {
        return $this->parkingId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getStartDate(): int
    {
        return $this->startDate;
    }

    public function getEndDate(): int
    {
        return $this->endDate;
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

    public function isValidAt(int $timestamp): bool
    {
        return $this->isActive && $timestamp >= $this->startDate && $timestamp <= $this->endDate;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }
}

