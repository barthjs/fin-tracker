<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CombinedFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[Table(name: 'combined_models')]
#[WithoutIncrementing]
final class Combined extends Model
{
    /** @use HasFactory<CombinedFactory> */
    use HasFactory;
}
