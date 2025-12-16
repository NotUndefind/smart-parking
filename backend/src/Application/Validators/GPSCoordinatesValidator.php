<?php

declare(strict_types=1);

namespace App\Application\Validators;

final class GPSCoordinatesValidator
{
    public function validate(float $latitude, float $longitude): void
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new \InvalidArgumentException('Latitude must be between -90 and 90 degrees');
        }

        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new \InvalidArgumentException('Longitude must be between -180 and 180 degrees');
        }
    }
}


