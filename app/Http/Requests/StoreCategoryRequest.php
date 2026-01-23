<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CategoryGroup;
use App\Http\Traits\HasDynamicPresenceRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCategoryRequest extends FormRequest
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
            'group' => [$p, Rule::enum(CategoryGroup::class)],
            'color' => [$p, 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})\b$/'],
            'is_active' => ['boolean'],
        ];
    }
}
