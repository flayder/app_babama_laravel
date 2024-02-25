<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('priority');
        });
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'category_id', 'id');
    }
}
