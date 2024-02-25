<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromocodeUse extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promocode_id',
    ];
}
