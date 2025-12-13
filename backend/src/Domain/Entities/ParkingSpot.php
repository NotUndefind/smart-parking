<?php

namespace App\Domain\Entities;

class ParkingSpot
{
    private int $id;
    private User $user;
    private string $startTime;
    private string $endTime;
    private Parking $parking;

    public function __construct(
        int $id,
        User $user,
        string $startTime,
        string $endTime,
        Parking $parking,
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->parking = $parking;
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function getParking(): Parking
    {
        return $this->parking;
    }

    // Update methods
    public function updateUser(User $user): void
    {
        $this->user = $user;
    }

    public function updateStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function updateEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }
}
