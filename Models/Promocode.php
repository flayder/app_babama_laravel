<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'discount_in',
        'limit',
        'count_remains',
        'count',
        'activity_id',
        'starts_at',
        'ends_at',
        'is_active',
        'code',
        'amount',
        'discount_in',
    ];
}
