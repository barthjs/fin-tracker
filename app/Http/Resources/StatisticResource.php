<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CategoryStatistic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CategoryStatistic
 */
final class StatisticResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'year' => $this->year,
            'jan' => $this->jan,
            'feb' => $this->feb,
            'mar' => $this->mar,
            'apr' => $this->apr,
            'may' => $this->may,
            'jun' => $this->jun,
            'jul' => $this->jul,
            'aug' => $this->aug,
            'sep' => $this->sep,
            'oct' => $this->oct,
            'nov' => $this->nov,
            'dec' => $this->dec,
            'yearly_sum' => $this->yearlySum(),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
