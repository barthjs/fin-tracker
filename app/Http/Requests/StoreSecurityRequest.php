<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SecurityType;
use App\Http\Traits\HasDynamicPresenceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSecurityRequest extends FormRequest
{
    use HasDynamicPresenceRule;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $p = $this->presenceRule();

        return [
            'name' => [$p, 'string', 'max:255'],
            'isin' => ['nullable', 'string', 'max:12'],
            'symbol' => ['nullable', 'string', 'max:10'],
            'type' => [$p, Rule::enum(SecurityType::class)],
            'price' => [$p, 'numeric'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => [$p, 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'],
            'is_active' => ['boolean'],
        ];
    }
}
