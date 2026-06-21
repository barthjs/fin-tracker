<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\SecurityRegion;
use App\Enums\SecuritySector;
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
            'region' => ['nullable', Rule::enum(SecurityRegion::class)],
            'sector' => ['nullable', Rule::enum(SecuritySector::class)],
            'country' => ['nullable', Rule::enum(Country::class)],
            'exchange' => ['nullable', 'string', 'max:50'],
            'ter' => ['nullable', 'numeric', 'min:0', 'max:99.999'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => [$p, 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'],
            'is_active' => ['boolean'],
        ];
    }
}
