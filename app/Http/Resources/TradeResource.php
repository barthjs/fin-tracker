<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trade
 */
final class TradeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date_time' => $this->date_time,
            'type' => $this->type,
            'total_amount' => $this->total_amount,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'fee' => $this->fee,
            'tax' => $this->tax,
            'notes' => $this->notes,
            'account_id' => $this->account_id,
            'portfolio_id' => $this->portfolio_id,
            'security_id' => $this->security_id,
        ];
    }
}
