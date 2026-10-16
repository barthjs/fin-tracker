<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Account;
use App\Traits\PreservesZeroFraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Account
 */
final class AccountResource extends JsonResource
{
    use PreservesZeroFraction;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'balance' => $this->balance,
            'currency' => $this->currency,
            'description' => $this->description,
            'logo' => $this->logo,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'notify_on_low_balance' => $this->notify_on_low_balance,
            'low_balance_threshold' => $this->low_balance_threshold,
            'low_balance_notified_at' => $this->low_balance_notified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
