<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentDetails extends Model
{
    use HasFactory, SoftDeletes;
    use Translatable;

    protected $guarded = ['id'];

    protected $casts = [
        'description' => 'object',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }
}
