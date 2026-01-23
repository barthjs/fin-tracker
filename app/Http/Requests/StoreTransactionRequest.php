<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Http\Traits\HasDynamicPresenceRule;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTransactionRequest extends FormRequest
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
            'type' => [$p, Rule::enum(TransactionType::class)],
            'amount' => [$p, 'numeric', 'min:0', 'max:1e9'],
            'payee' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'account_id' => [$p, Rule::exists(Account::class, 'id')],
            'transfer_account_id' => [
                'prohibited_unless:type,'.TransactionType::Transfer->value,
                'required_if:type,'.TransactionType::Transfer->value,
                'nullable',
                Rule::exists(Account::class, 'id'),
                'different:account_id',
            ],
            'category_id' => [$p, Rule::exists(Category::class, 'id')],
        ];
    }
}
