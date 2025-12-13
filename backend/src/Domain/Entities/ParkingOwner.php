<?php

namespace App\Domain\Entities;

class ParkingOwner
{
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $password;
    private Parking $parking; // Association to Parking entity

    public function __construct(
        int $id,
        string $firstName,
        string $lastName,
        string $email,
        string $password,
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->password = $password;
    }

    // Getter methods
    public function getId(): int
    {
        return $this->id;
    }
    public function getFirstName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    // Update methods
    public function updateFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function updateLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function updateEmail(string $email): void
    {
        $this->email = $email;
    }

    public function updatePassword(string $password): void
    {
        $this->password = $password;
    }
}
