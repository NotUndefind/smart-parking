<?php

declare(strict_types=1);

namespace App\Application\Validators;

final class TimeSlotValidator
{
    public function validate(int $startTime, int $endTime): void
    {
        if ($startTime <= 0 || $endTime <= 0) {
            throw new \InvalidArgumentException('Start and end time must be positive timestamps');
        }

        if ($endTime <= $startTime) {
            throw new \InvalidArgumentException('End time must be greater than start time');
        }

        // Optionnel : refuser les créneaux trop longs (ex: > 48h)
        $maxDurationSeconds = 48 * 3600;
        if (($endTime - $startTime) > $maxDurationSeconds) {
            throw new \InvalidArgumentException('Time slot duration is too long');
        }
    }
}


