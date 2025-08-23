<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Combined extends Model
{
    public $incrementing = false;

    protected $table = 'combined_models';
}
