<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Security;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Security
 */
final class SecurityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'isin' => $this->isin,
            'symbol' => $this->symbol,
            'type' => $this->type,
            'price' => $this->price,
            'total_quantity' => $this->total_quantity,
            'market_value' => $this->market_value,
            'description' => $this->description,
            'logo' => $this->logo,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
