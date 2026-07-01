<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;

/**
 * Spending of a budget within one period, the period end is exclusive.
 */
final readonly class BudgetUsage
{
    public function __construct(
        public CarbonImmutable $periodStart,
        public CarbonImmutable $periodEnd,
        public float $budgeted,
        public float $spent,
        public float $remaining,
        public float $percentage,
    ) {}

    /**
     * Returns the last day that belongs to the period, since the period end is exclusive.
     */
    public function lastDay(): CarbonImmutable
    {
        return $this->periodEnd->subDay();
    }

    /**
     * Whether the spending has reached or passed the given percentage threshold.
     */
    public function hasReachedThreshold(int $threshold): bool
    {
        return $this->percentage >= $threshold;
    }
}
