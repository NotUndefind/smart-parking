<?php

namespace App\Domain\Entities;

class User
{
    private int $id;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;

    /**
     * @var Reservation[]
     */
    private array $reservations = []; // Association to Reservation entity

    /**
     * @var ParkingSpot[]
     */
    private array $parkingSpots; // Association to ParkingSpot entity

    public function __construct(
        int $id,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . " " . $this->lastName;
    }

    // Update methods
    public function updatePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function updateEmail(string $newEmail): void
    {
        $this->email = $newEmail;
    }

    public function updateFirstName(string $newFirstName): void
    {
        $this->firstName = $newFirstName;
    }

    public function updateLastName(string $newLastName): void
    {
        $this->lastName = $newLastName;
    }

    public function getReservations(): array
    {
        return $this->reservations;
    }

    public function getParkingSpots(): array
    {
        return $this->parkingSpots;
    }
}
