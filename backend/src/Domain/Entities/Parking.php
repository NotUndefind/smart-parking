<?php

namespace App\Domain\Entities;

class Parking
{
    private int $id;
    private string $location;
    private int $capacity;
    private float $pricePerQuarterHour;
    private string $openingHours;
    private Reservation $reservations; // Association to Reservation entity
    private ParkingSpot $parkingSpots; // Association to ParkingSpot entity

    public function __construct(
        int $id,
        string $location,
        int $capacity,
        float $pricePerQuarterHour,
        string $openingHours,
    ) {
        $this->id = $id;
        $this->location = $location;
        $this->capacity = $capacity;
        $this->pricePerQuarterHour = $pricePerQuarterHour;
        $this->openingHours = $openingHours;
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getPricePerQuarterHour(): float
    {
        return $this->pricePerQuarterHour;
    }

    public function getOpeningHours(): string
    {
        return $this->openingHours;
    }

    //Update methods

    public function updateLocation(string $location): void
    {
        $this->location = $location;
    }

    public function updateCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function updatePricePerQuarterHour(float $pricePerQuarterHour): void
    {
        $this->pricePerQuarterHour = $pricePerQuarterHour;
    }

    public function updateOpeningHours(string $openingHours): void
    {
        $this->openingHours = $openingHours;
    }
}
