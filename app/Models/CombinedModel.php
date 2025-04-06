<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CombinedModel extends Model
{
    protected $table = 'combined_models';

    public $incrementing = false;
}
