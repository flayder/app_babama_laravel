<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'languages';
    protected $guarded = ['id'];

    public function mailTemplates()
    {
        return $this->hasMany(EmailTemplate::class, 'language_id');
    }

    public function notifyTemplates()
    {
        return $this->hasMany(NotifyTemplate::class, 'language_id');
    }

    public function contentDetails()
    {
        return $this->hasMany(ContentDetails::class, 'language_id');
    }
}
