<?php

namespace App\Domain\Entities;

class Subscription
{
    private int $id;
    private User $user;
    private Parking $parking;
    private string $type;
    private float $price;
    private int $duration; // Duration in months
    private array $timeSlots; // Array of TimeSlot entities

    public function __construct(
        int $id,
        string $type,
        float $price,
        int $duration,
        array $timeSlots = [],
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->price = $price;
        $this->duration = $duration;
        $this->timeSlots = $timeSlots;
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    // Update methods
    public function updateType(string $type): void
    {
        $this->type = $type;
    }

    public function updatePrice(float $price): void
    {
        $this->price = $price;
    }

    public function updateDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function updateTimeSlots(array $timeSlots): void
    {
        $this->timeSlots = $timeSlots;
    }
}
