<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'any' => 'json',
        'female' => 'json',
        'male' => 'json',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
