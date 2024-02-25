<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public $appends = ['provider_name'];

    protected $fillable = [
        'activity_id',
        'category_id',
        'service_status',
        'priority',
        'service_title',
        'price',
        'min_amount',
        'max_amount',
        'api_provider_id',
        'api_service_id',
        'amount_percent',
        'short_description',
        'service_description',
        'link_demo',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('priority');
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function provider()
    {
        return $this->belongsTo(ApiProvider::class, 'api_provider_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'service_id', 'id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    protected function scopeUserRate($query): void
    {
        $query->addSelect(['user_rate' => UserServiceRate::select('price')
            ->whereColumn('service_id', 'services.id')
            ->where('user_id', auth()->id()),
        ]);
    }

    public function getProviderNameAttribute($value)
    {
        if (isset($this->api_provider_id) && 0 != $this->api_provider_id) {
            $prov = ApiProvider::find($this->api_provider_id);
            if ($prov) {
                return $prov['api_name'];
            }

            return false;
        }
    }

    public function parameters(): HasMany
    {
        return $this->hasMany(ServiceParameter::class)
            ->where(function ($query) {
                $query->whereNotNull('any')
                    ->orWhereNotNull('male')
                    ->orWhereNotNull('female');
            });
    }
}
