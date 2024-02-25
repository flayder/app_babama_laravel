<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'email_templates';

    protected $guarded = ['id'];
    protected $casts = [
        'short_keys' => 'object',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
