<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property float $price
 */

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\.000\Z', // Свой формат
    ];
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parameter(): HasOne
    {
        return $this->hasOne(ServiceParameter::class,'id','service_parameter_id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class,'id','category_id');
    }

    public function serviceParameter(): HasOne
    {
        return $this->hasOne(ServiceParameter::class,'id','service_parameter_id');
    }

    public function promocode(): HasOne
    {
        return $this->hasOne(Promocode::class,'id','promocode_id');
    }
}
