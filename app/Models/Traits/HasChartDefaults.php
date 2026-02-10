<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Contracts\Chartable;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model&Chartable
 */
trait HasChartDefaults
{
    public function getKey(): string
    {
        return $this->id;
    }

    public function getChartLabel(): string
    {
        return $this->name;
    }

    public function getChartColor(): string
    {
        return $this->color;
    }
}
