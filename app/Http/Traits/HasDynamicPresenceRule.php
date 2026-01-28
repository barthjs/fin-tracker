<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @mixin FormRequest
 */
trait HasDynamicPresenceRule
{
    /**
     * * @return string 'sometimes'|'required'
     */
    protected function presenceRule(): string
    {
        return ($this->isMethod('PATCH') || $this->isMethod('PUT'))
            ? 'sometimes'
            : 'required';
    }
}
