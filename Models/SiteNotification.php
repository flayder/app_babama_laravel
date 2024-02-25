<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteNotification extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'description' => 'object',
    ];

    protected $appends = ['formatted_date'];

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('F d, Y h:i A');
    }

    public function siteNotificational()
    {
        return $this->morphTo();
    }
}
