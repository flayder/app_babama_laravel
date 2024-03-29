<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Traits\ContentDelete;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use ContentDelete;
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function contentDetails()
    {
        return $this->hasMany(ContentDetails::class, 'content_id', 'id');
    }

    public function language()
    {
        return $this->hasMany(Language::class, 'language_id', 'id');
    }

    public function contentMedia()
    {
        return $this->hasOne(ContentMedia::class, 'content_id', 'id');
    }
}
