<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Domain\Entities\{User, Owner, Parking, ParkingSpot, Reservation, Stationnement, Subscription};

class EntityFactory
{
    public static function createUser(array $overrides = []): User
    {
        return new User(
            id: $overrides['id'] ?? 'user_test_' . uniqid(),
            email: $overrides['email'] ?? 'test@example.com',
            passwordHash: $overrides['passwordHash'] ?? '$2y$12$' . str_repeat('a', 53),
            firstName: $overrides['firstName'] ?? 'John',
            lastName: $overrides['lastName'] ?? 'Doe',
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createOwner(array $overrides = []): Owner
    {
        return new Owner(
            id: $overrides['id'] ?? 'owner_test_' . uniqid(),
            email: $overrides['email'] ?? 'owner@example.com',
            passwordHash: $overrides['passwordHash'] ?? '$2y$12$' . str_repeat('a', 53),
            companyName: $overrides['companyName'] ?? 'Test Company',
            firstName: $overrides['firstName'] ?? 'Jane',
            lastName: $overrides['lastName'] ?? 'Smith',
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createParking(array $overrides = []): Parking
    {
        return new Parking(
            id: $overrides['id'] ?? 'parking_test_' . uniqid(),
            ownerId: $overrides['ownerId'] ?? 'owner_1',
            name: $overrides['name'] ?? 'Test Parking',
            address: $overrides['address'] ?? '123 Test St',
            latitude: $overrides['latitude'] ?? 48.8566,
            longitude: $overrides['longitude'] ?? 2.3522,
            totalSpots: $overrides['totalSpots'] ?? 100,
            tariffs: $overrides['tariffs'] ?? [
                ['start_hour' => 0, 'end_hour' => 24, 'price_per_hour' => 2.5]
            ],
            schedule: $overrides['schedule'] ?? [
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '20:00'],
                'saturday' => ['open' => '09:00', 'close' => '18:00'],
                'sunday' => ['open' => '09:00', 'close' => '18:00']
            ],
            isActive: $overrides['isActive'] ?? true,
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createReservation(array $overrides = []): Reservation
    {
        $now = time();

        return new Reservation(
            id: $overrides['id'] ?? 'reservation_test_' . uniqid(),
            userId: $overrides['userId'] ?? 'user_1',
            parkingId: $overrides['parkingId'] ?? 'parking_1',
            startTime: $overrides['startTime'] ?? $now,
            endTime: $overrides['endTime'] ?? $now + 3600,
            estimatedPrice: $overrides['estimatedPrice'] ?? 5.0,
            status: $overrides['status'] ?? 'active',
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createStationnement(array $overrides = []): Stationnement
    {
        $now = time();

        return new Stationnement(
            id: $overrides['id'] ?? 'stationnement_test_' . uniqid(),
            userId: $overrides['userId'] ?? 'user_1',
            parkingId: $overrides['parkingId'] ?? 'parking_1',
            reservationId: $overrides['reservationId'] ?? null,
            subscriptionId: $overrides['subscriptionId'] ?? null,
            entryTime: $overrides['entryTime'] ?? $now,
            exitTime: $overrides['exitTime'] ?? null,
            finalPrice: $overrides['finalPrice'] ?? 0.0,
            penaltyAmount: $overrides['penaltyAmount'] ?? 0.0,
            status: $overrides['status'] ?? 'active',
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createSubscription(array $overrides = []): Subscription
    {
        $now = time();

        return new Subscription(
            id: $overrides['id'] ?? 'subscription_test_' . uniqid(),
            parkingId: $overrides['parkingId'] ?? 'parking_1',
            userId: $overrides['userId'] ?? 'user_1',
            type: $overrides['type'] ?? 'monthly',
            price: $overrides['price'] ?? 50.0,
            startDate: $overrides['startDate'] ?? $now,
            endDate: $overrides['endDate'] ?? $now + (30 * 24 * 3600),
            isActive: $overrides['isActive'] ?? true,
            createdAt: $overrides['createdAt'] ?? null,
            updatedAt: $overrides['updatedAt'] ?? null
        );
    }

    public static function createParkingSpot(array $overrides = []): ParkingSpot
    {
        return new ParkingSpot(
            id: $overrides['id'] ?? 1,
            user: $overrides['user'] ?? self::createUser(),
            startTime: $overrides['startTime'] ?? '08:00',
            endTime: $overrides['endTime'] ?? '18:00',
            parking: $overrides['parking'] ?? self::createParking()
        );
    }
}
