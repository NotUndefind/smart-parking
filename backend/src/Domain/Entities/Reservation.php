<?php

namespace App\Domain\Entities;

class Reservation
{
    private int $id;
    private User $user;
    private ParkingSpot $parkingSpot;
    private string $startTime;
    private string $endTime;

    public function __construct(
        int $id,
        User $user,
        ParkingSpot $parkingSpot,
        string $startTime,
        string $endTime,
    ) {
        $this->id = $id;
        $this->user = $user;
        $this->parkingSpot = $parkingSpot;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
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

    public function getParkingSpot(): ParkingSpot
    {
        return $this->parkingSpot;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    // Update methods
    public function updateStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function updateEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function updateParkingSpot(ParkingSpot $parkingSpot): void
    {
        $this->parkingSpot = $parkingSpot;
    }

    public function updateUser(User $user): void
    {
        $this->user = $user;
    }
}
