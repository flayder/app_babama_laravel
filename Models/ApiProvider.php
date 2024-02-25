<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiProvider extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

    public function services()
    {
        return $this->hasMany(Service::class, 'api_provider_id');
    }
}
