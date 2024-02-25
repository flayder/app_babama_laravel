<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    use Translatable;

    protected $casts = [
        'description' => 'object',
    ];

    public function scopeTemplateMedia()
    {
        $media = TemplateMedia::where('section_name', $this->section_name)->first();
        if (!$media) {
            return null;
        }

        return $media->description;
    }
}
