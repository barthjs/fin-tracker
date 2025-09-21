<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * This model is only used as a wrapper for the union query
 * on the dashboard table widget.
 *
 * @property-read string $id
 * @property-read string $name
 * @property-read string $type
 * @property-read string|null $logo
 */
final class Combined extends Model
{
    public $incrementing = false;

    protected $table = 'combined_models';
}
