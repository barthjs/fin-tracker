<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TradeType;
use App\Http\Traits\HasDynamicPresenceRule;
use App\Models\Account;
use App\Models\Portfolio;
use App\Models\Security;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTradeRequest extends FormRequest
{
    use HasDynamicPresenceRule;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $p = $this->presenceRule();

        return [
            'date_time' => [$p, 'date'],
            'type' => [$p, Rule::enum(TradeType::class)],
            'quantity' => [$p, 'numeric', 'min:0'],
            'price' => [$p, 'numeric', 'min:0', 'max:1e9'],
            'fee' => [$p, 'numeric', 'min:0', 'max:1e9'],
            'tax' => [$p, 'numeric', 'min:0', 'max:1e9'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'account_id' => [$p, Rule::exists(Account::class, 'id')],
            'portfolio_id' => [$p, Rule::exists(Portfolio::class, 'id')],
            'security_id' => [$p, Rule::exists(Security::class, 'id')],
        ];
    }
}
