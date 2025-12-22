<?php

declare(strict_types=1);

namespace App\Application\DTOs\Output;

final class MonthlyRevenueOutput
{
    public function __construct(
        public readonly float $totalRevenue,
        public readonly float $reservationsRevenue,
        public readonly float $stationnementsRevenue,
        public readonly float $subscriptionsRevenue,
        public readonly float $penaltiesRevenue,
        public readonly int $reservationsCount
    ) {
    }

    public function toArray(): array
    {
        return [
            'total_revenue' => $this->totalRevenue,
            'reservations_revenue' => $this->reservationsRevenue,
            'stationnements_revenue' => $this->stationnementsRevenue,
            'subscriptions_revenue' => $this->subscriptionsRevenue,
            'penalties_revenue' => $this->penaltiesRevenue,
            'reservations_count' => $this->reservationsCount,
        ];
    }
}
