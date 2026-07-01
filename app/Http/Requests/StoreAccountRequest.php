<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Http\Traits\HasDynamicPresenceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class StoreAccountRequest extends FormRequest
{
    use HasDynamicPresenceRule;

    /**
     * @return array<string, list<Enum|string>>
     */
    public function rules(): array
    {
        $p = $this->presenceRule();

        return [
            'name' => [$p, 'string', 'max:255'],
            'currency' => [$p, Rule::enum(Currency::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => [$p, 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'],
            'is_active' => ['boolean'],
            'notify_on_low_balance' => ['boolean'],
            'low_balance_threshold' => ['required_if:notify_on_low_balance,true', 'numeric', 'min:-1e9', 'max:1e9'],
        ];
    }
}
