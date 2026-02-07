<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\NotificationProviderType;
use App\Models\NotificationTarget;
use Illuminate\Support\Facades\DB;

final readonly class NotificationTargetService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): NotificationTarget
    {
        return DB::transaction(function () use ($data): NotificationTarget {
            $data['configuration'] ??= [];

            return NotificationTarget::create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(NotificationTarget $target, array $data): NotificationTarget
    {
        if (! array_key_exists('configuration', $data) && $target->type === NotificationProviderType::DATABASE) {
            $data['configuration'] = [];
        }

        return DB::transaction(function () use ($target, $data): NotificationTarget {
            $target->update($data);

            return $target;
        });
    }
}
